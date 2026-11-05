<?php

namespace Tests\Feature;

use App\Filament\Pages\SeoDashboard;
use App\Filament\Pages\SeoSettings;
use App\Filament\Resources\SeoPages\Pages\ManageSeoPages;
use App\Models\BlogPost;
use App\Models\SeoAudit;
use App\Models\SeoPage;
use App\Models\SeoSetting;
use App\Models\User;
use App\Services\SeoAuditor;
use App\Services\SeoPublicVerifier;
use App\Services\SiteSeo;
use Filament\Actions\Testing\TestAction;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Tests\TestCase;

class SeoDashboardTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_seo_administration_is_private_and_works_for_existing_admins_in_production(): void
    {
        foreach (['/admin/seo-dashboard', '/admin/seo-settings', '/admin/seo-pages'] as $path) {
            $this->get($path)->assertRedirect('/admin/login');
        }
        $this->app->instance('env', 'production');
        $this->actingAs(User::factory()->create());
        $this->get('/admin/seo-dashboard')->assertOk()->assertSeeText('Tu punto de partida')->assertHeader('X-Robots-Tag', 'noindex, nofollow');
        $this->get('/admin/seo-settings')->assertOk()->assertSeeText('HostGator');
        $this->get('/admin/seo-pages')->assertOk();
        $this->assertDatabaseCount('seo_pages', 20);
    }

    public function test_analysis_saves_real_page_results_and_a_score_with_history(): void
    {
        $this->freezeTime();
        $post = BlogPost::factory()->create();
        $this->actingAs(User::factory()->create());
        Livewire::test(SeoDashboard::class)->call('analyze')->assertSee('de 100');
        $audit = SeoAudit::firstOrFail();
        $this->assertSame(21, $audit->page_count);
        $this->assertGreaterThanOrEqual(1, $audit->score);
        $this->assertLessThanOrEqual(100, $audit->score);
        $this->assertCount(21, $audit->report['pages']);
        $this->assertNotEmpty(collect($audit->report['pages'])->firstWhere('path', $post->path)['checks']);
        $this->assertFalse(collect($audit->report['global'])->firstWhere('key', 'public')['passed']);
        $this->assertFalse(collect($audit->report['global'])->firstWhere('key', 'indexing')['passed']);
        $this->travel(1)->minute();
        $this->artisan('seo:audit', ['--json' => true])->assertSuccessful();
        $this->assertDatabaseCount('seo_audits', 2);
    }

    public function test_correcting_a_title_improves_the_score_and_does_not_overweight_the_blog(): void
    {
        app(SiteSeo::class)->initialize();
        $page = SeoPage::where('route_name', 'home')->where('locale', 'es')->firstOrFail();
        $page->update(['title' => 'X']);
        $auditor = app(SeoAuditor::class);
        $before = $auditor->run();
        $bad = collect($before->report['pages'])->firstWhere('path', '/');
        $this->assertFalse(collect($bad['checks'])->firstWhere('key', 'title')['passed']);
        $page->update(['title' => 'Estrategia, creatividad y tecnología | Feat.']);
        $after = $auditor->run();
        $good = collect($after->report['pages'])->firstWhere('path', '/');
        $this->assertSame(100, $good['score']);
        $this->assertSame($bad['score'] + 10, $good['score']);
        $this->assertGreaterThan($before->report['pages_score'], $after->report['pages_score']);
    }

    public function test_score_and_history_show_filtered_pages_and_untrusted_titles_are_escaped(): void
    {
        $post = BlogPost::factory()->create(['title' => '<script>alert("audit")</script>']);
        $this->actingAs(User::factory()->create());
        app(SeoAuditor::class)->run();
        Livewire::test(SeoDashboard::class)->set('search', $post->slug)->assertSee($post->slug)->assertDontSeeHtml('<script>alert("audit")</script>');
        $this->get('/admin')->assertOk()->assertSeeText('SEO y búsquedas con IA')->assertSeeText('Ver dashboard SEO');
    }

    public function test_settings_save_verification_tokens_and_invalidate_stale_public_verification(): void
    {
        $this->actingAs(User::factory()->create());
        SeoSetting::create(['id' => 1, 'data' => ['public_verification' => ['passed' => true]]]);
        Livewire::test(SeoSettings::class)->fillForm(['public_url' => 'https://feat.example.com', 'indexing_enabled' => true, 'default_image' => '/assets/feat-social.png', 'search_console_verification' => 'valid-token-123', 'bing_verification' => 'ABC123'])->call('save')->assertHasNoFormErrors();
        $settings = SeoSetting::firstOrFail()->data;
        $this->assertSame('https://feat.example.com', $settings['public_url']);
        $this->assertArrayNotHasKey('public_verification', $settings);
        $this->get('/')->assertSee('name="google-site-verification" content="valid-token-123"', false)->assertSee('https://feat.example.com/', false);
    }

    public function test_settings_reject_local_domains_paths_and_scriptable_images(): void
    {
        $this->actingAs(User::factory()->create());
        Livewire::test(SeoSettings::class)->fillForm(['public_url' => 'https://localhost/private', 'default_image' => 'javascript:alert(1)'])->call('save')->assertHasFormErrors(['public_url', 'default_image']);
        $this->assertDatabaseCount('seo_settings', 0);
    }

    public function test_editor_can_change_page_metadata_without_changing_its_route_or_locale(): void
    {
        $this->actingAs(User::factory()->create());
        app(SiteSeo::class)->initialize();
        $page = SeoPage::where('route_name', 'makitha')->where('locale', 'es')->firstOrFail();
        Livewire::test(ManageSeoPages::class)->callAction(TestAction::make('edit')->table($page), data: ['title' => 'Makitha: soluciones de tecnología | Feat.', 'description' => 'Desarrollo e integraciones con Makitha, la unidad de tecnología de Feat. Conoce nuestras soluciones para tu negocio.', 'route_name' => 'home', 'locale' => 'en'])->assertHasNoFormErrors();
        $this->assertSame('makitha', $page->fresh()->route_name);
        $this->assertSame('es', $page->fresh()->locale);
        $this->get('/makitha')->assertSee('<title>Makitha: soluciones de tecnología | Feat.</title>', false);
    }

    public function test_verification_refuses_an_old_site_and_never_awards_it_as_a_verified_release(): void
    {
        Http::preventStrayRequests();
        Http::fake(['https://www.featbrands.com/' => Http::response('<html><title>Sitio anterior</title></html>'), 'https://www.featbrands.com/robots.txt' => Http::response('User-agent: *'), 'https://www.featbrands.com/sitemap.xml' => Http::response('<sitemapindex/>')]);
        $verifier = $this->partialMock(SeoPublicVerifier::class);
        $verifier->shouldReceive('addresses')->once()->with('www.featbrands.com')->andReturn(['8.8.8.8']);
        $report = $verifier->verify();
        $this->assertFalse($report['passed']);
        $this->assertCount(5, $report['checks']);
        $this->assertFalse(SeoSetting::firstOrFail()->data['public_verification']['passed']);
        Http::assertSentCount(5);
    }

    public function test_public_verification_passes_only_for_the_new_release_and_expires(): void
    {
        $this->freezeTime();
        $this->app->instance('env', 'production');
        config(['app.debug' => false]);
        Http::preventStrayRequests();
        Http::fake(['https://www.featbrands.com/' => Http::response('<meta name="feat-site-id" content="featbrands-laravel-v1"><meta name="robots" content="index, follow">'), 'https://www.featbrands.com/robots.txt' => Http::response("User-agent: OAI-SearchBot\nUser-agent: PerplexityBot\nAllow: /\n"), 'https://www.featbrands.com/sitemap.xml' => Http::response('<sitemapindex><loc>https://www.featbrands.com/blog-posts-sitemap.xml</loc></sitemapindex>')]);
        $verifier = $this->partialMock(SeoPublicVerifier::class);
        $verifier->shouldReceive('addresses')->once()->andReturn(['8.8.8.8']);
        $this->assertTrue($verifier->verify()['passed']);
        $audit = app(SeoAuditor::class)->run();
        $this->assertTrue(collect($audit->report['global'])->firstWhere('key', 'public')['passed']);
        $this->travel(8)->days();
        $audit = app(SeoAuditor::class)->run();
        $this->assertFalse(collect($audit->report['global'])->firstWhere('key', 'public')['passed']);
        Http::assertSentCount(5);
    }

    public function test_settings_payload_cannot_forge_public_verification_or_a_score(): void
    {
        $this->actingAs(User::factory()->create());
        Livewire::test(SeoSettings::class)->set('data.public_verification', ['passed' => true, 'origin' => 'https://www.featbrands.com'])->set('data.score', 100)->call('save')->assertHasNoFormErrors();
        $saved = SeoSetting::firstOrFail()->data;
        $this->assertArrayNotHasKey('public_verification', $saved);
        $this->assertArrayNotHasKey('score', $saved);
    }

    public function test_a_missing_local_sharing_image_is_reported_by_the_dashboard(): void
    {
        $post = BlogPost::factory()->create(['cover_image' => '/uploads/no-existe.jpg']);
        $audit = app(SeoAuditor::class)->run();
        $page = collect($audit->report['pages'])->firstWhere('path', $post->path);
        $this->assertFalse(collect($page['checks'])->firstWhere('key', 'sharing')['passed']);
    }

    public function test_private_ip_targets_are_not_requested(): void
    {
        Http::preventStrayRequests();
        SeoSetting::create(['id' => 1, 'data' => ['public_url' => 'https://127.0.0.1']]);
        $this->assertFalse(app(SeoPublicVerifier::class)->verify()['passed']);
        Http::assertNothingSent();
    }
}
