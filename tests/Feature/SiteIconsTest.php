<?php

namespace Tests\Feature;

use App\Filament\Pages\ManageIcons;
use App\Models\PageSection;
use App\Models\User;
use App\Services\SiteContent;
use App\Services\SiteIcons;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class SiteIconsTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_pages_keep_their_original_icons_until_one_is_changed(): void
    {
        $this->get('/makitha')->assertOk()->assertSee('<i aria-hidden="true">⌘</i>', false);
        $this->get('/')->assertOk()->assertSee('<svg width="22" height="24" viewBox="0 0 22 24"', false);
        $this->get('/nosotros')->assertOk()->assertSee('–</span>', false);
    }

    public function test_a_symbol_saved_in_the_cms_replaces_the_icon_on_the_site(): void
    {
        $this->actingAs(User::factory()->create());

        Livewire::test(ManageIcons::class)
            ->set('data.icons.makitha.engine_1.text', '★')
            ->set('data.icons.general.arrow_out.text', '⇗')
            ->call('save')
            ->assertHasNoFormErrors();

        $this->get('/makitha')->assertSee('<i aria-hidden="true">★</i>', false)->assertDontSee('<i aria-hidden="true">⌘</i>', false);
        $this->get('/nosotros')->assertSee('⇗')->assertDontSee('↗');
    }

    public function test_an_uploaded_image_replaces_a_drawn_icon(): void
    {
        Storage::fake('public');
        UploadedFile::fake()->image('play.png')->storeAs('site/icons', 'play.png', 'public');
        PageSection::query()->create(['page' => SiteContent::PAGE, 'section' => SiteIcons::SECTION, 'content_es' => ['home' => ['play' => ['image' => 'site/icons/play.png']]], 'content_en' => []]);

        $this->get('/')
            ->assertSee('<img src="'.Storage::disk('public')->url('site/icons/play.png').'" alt="" aria-hidden="true" class="site-icon" style="width:22px;height:24px;">', false)
            ->assertDontSee('<svg width="22" height="24" viewBox="0 0 22 24"', false);
    }

    public function test_only_known_icons_and_filled_values_are_saved(): void
    {
        app(SiteIcons::class)->save([
            'social' => ['metric' => ['text' => ' • ', 'image' => null], 'reply' => ['text' => '', 'image' => null]],
            'unknown' => ['icon' => ['text' => 'x']],
        ]);

        $this->assertSame(['social' => ['metric' => ['text' => '•']]], app(SiteIcons::class)->stored());
    }

    public function test_the_preview_shows_the_page_of_the_icon_being_edited(): void
    {
        $this->actingAs(User::factory()->create());
        $page = Livewire::test(ManageIcons::class)->set('data.icons.social.metric.text', '✦');

        $preview = $page->instance()->renderPreview('es', 'data.icons.social.metric.text');

        $this->assertSame('/social-media-strategies?lang=es', $preview['path']);
        $this->assertStringContainsString('<span aria-hidden="true">✦</span>', $preview['html']);
        $this->assertSame([], app(SiteIcons::class)->stored());
    }
}
