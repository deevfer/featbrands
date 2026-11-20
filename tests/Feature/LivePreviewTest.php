<?php

namespace Tests\Feature;

use App\Filament\Pages\ManageCases;
use App\Filament\Pages\ManageGeoPages;
use App\Filament\Pages\ManageHomeHero;
use App\Filament\Pages\ManageSocial;
use App\Filament\Resources\BlogPosts\Pages\EditBlogPost;
use App\Models\BlogPost;
use App\Models\PageSection;
use App\Models\User;
use Database\Seeders\HomeHeroSeeder;
use Database\Seeders\HomeProofSeeder;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class LivePreviewTest extends TestCase
{
    use LazilyRefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->create());
    }

    public function test_preview_shows_unsaved_changes_without_saving_them(): void
    {
        $page = Livewire::test(ManageSocial::class)->set('data.c0.es.title', 'Borrador sin guardar.');

        $preview = $page->instance()->renderPreview('es', 'data.c0.es.title');

        $this->assertSame(200, $preview['status']);
        $this->assertSame('/social-media-strategies?lang=es', $preview['path']);
        $this->assertStringContainsString('Borrador sin guardar.', $preview['html']);
        $this->assertDatabaseCount('page_sections', 0);
        $this->assertNull(session('filament.notifications'));
        $this->get('/social-media-strategies')->assertDontSee('Borrador sin guardar.');
    }

    public function test_preview_follows_the_language_and_the_page_being_edited(): void
    {
        $page = Livewire::test(ManageGeoPages::class)->set('data.c1.es.h1', 'Comunicación de marca, en borrador');

        $preview = $page->instance()->renderPreview('en', 'data.c1.es.h1');

        $this->assertSame('/mejorar-comunicacion-marca-guatemala', $preview['path']);
        $this->assertStringContainsString('<h1>Comunicación de marca, en borrador</h1>', $preview['html']);
        $this->assertStringContainsString('lang="en"', Livewire::test(ManageCases::class)->instance()->renderPreview('en')['html']);
    }

    public function test_pages_with_their_own_cms_are_previewed_too(): void
    {
        $this->seed([HomeHeroSeeder::class, HomeProofSeeder::class]);
        $saved = PageSection::query()->where('page', 'home')->pluck('content_es', 'section');
        $page = Livewire::test(ManageHomeHero::class)->set('data.hero_es.title', 'Crecimiento en borrador');

        $preview = $page->instance()->renderPreview('es');

        $this->assertStringContainsString('Crecimiento en', $preview['html']);
        $this->assertStringContainsString('borrador', $preview['html']);
        $this->assertEquals($saved, PageSection::query()->where('page', 'home')->pluck('content_es', 'section'));
    }

    public function test_saving_the_home_keeps_the_client_logos_bundled_with_the_site(): void
    {
        $this->seed([HomeHeroSeeder::class, HomeProofSeeder::class]);

        Livewire::test(ManageHomeHero::class)->call('save')->assertHasNoFormErrors();

        $clients = PageSection::query()->where('page', 'home')->where('section', 'collaboration')->value('content_es')['clients'];
        $this->assertCount(44, $clients);
        $this->assertSame('assets/client-logos/beiersdorf.webp', $clients[0]['logo']);
        $this->get('/')->assertSee('src="'.asset('assets/client-logos/beiersdorf.webp').'"', false);
    }

    public function test_invalid_values_do_not_break_the_preview_or_the_form(): void
    {
        $post = BlogPost::factory()->create();
        $page = Livewire::test(EditBlogPost::class, ['record' => $post->getRouteKey()])->set('data.title', '');

        $this->assertSame(422, $page->instance()->renderPreview()['status']);
        $page->assertHasNoErrors();
    }

    public function test_unpublished_posts_can_be_previewed_and_saving_afterwards_still_works(): void
    {
        $post = BlogPost::factory()->create(['is_published' => false, 'slug' => 'borrador', 'path' => '/post/borrador']);
        $page = Livewire::test(EditBlogPost::class, ['record' => $post->getRouteKey()])->set('data.title', 'Título en revisión');

        $preview = $page->instance()->renderPreview();

        $this->assertSame(200, $preview['status']);
        $this->assertStringContainsString('Título en revisión', $preview['html']);
        $this->assertFalse($post->fresh()->is_published);
        $this->assertNotSame('Título en revisión', $post->fresh()->title);

        $page->call('save')->assertHasNoFormErrors();

        $this->assertSame('Título en revisión', $post->fresh()->title);
        $this->assertFalse($post->fresh()->is_published);
    }

    public function test_every_cms_page_shows_the_preview_panel(): void
    {
        $this->seed([HomeHeroSeeder::class, HomeProofSeeder::class]);
        $this->get('/admin/manage-home-hero')->assertOk()->assertSee('x-data="cmsPreview(', false)->assertSee('js/cms-preview.js', false);
        $this->get('/admin/manage-navigation')->assertOk()->assertSee('x-data="cmsPreview(', false);
        $this->get('/admin/blog-posts/'.BlogPost::factory()->create()->getRouteKey().'/edit')->assertOk()->assertSee('x-data="cmsPreview(', false);
    }

    public function test_seo_fields_show_how_the_result_would_look_in_google(): void
    {
        $post = BlogPost::factory()->create(['title' => 'Título del artículo', 'seo_title' => null, 'slug' => 'titulo', 'path' => '/post/titulo']);

        Livewire::test(EditBlogPost::class, ['record' => $post->getRouteKey()])
            ->assertSee('Vista previa en Google')
            ->assertSee('Título del artículo')
            ->set('data.seo_title', str_repeat('Largo ', 12))
            ->assertSee('Título: 71/60')
            ->assertSee('www.featbrands.com › post › titulo');
    }
}
