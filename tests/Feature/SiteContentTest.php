<?php

namespace Tests\Feature;

use App\Filament\Pages\ManageAbout;
use App\Filament\Pages\ManageBlogTexts;
use App\Filament\Pages\ManageCases;
use App\Filament\Pages\ManageContactPage;
use App\Filament\Pages\ManageGeoPages;
use App\Filament\Pages\ManageNavigation;
use App\Filament\Pages\ManageSocial;
use App\Models\PageSection;
use App\Models\User;
use App\Services\SiteContent;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class SiteContentTest extends TestCase
{
    use LazilyRefreshDatabase;

    /** @return array<string, array{class-string, string}> */
    public static function adminPages(): array
    {
        return [
            'social' => [ManageSocial::class, '/social-media-strategies'],
            'casos' => [ManageCases::class, '/casos'],
            'nosotros' => [ManageAbout::class, '/nosotros'],
            'contacto' => [ManageContactPage::class, '/contacto'],
            'blog' => [ManageBlogTexts::class, '/blog'],
            'GEO' => [ManageGeoPages::class, '/mejor-agencia-publicidad-guatemala'],
            'menú y footer' => [ManageNavigation::class, '/'],
        ];
    }

    #[DataProvider('adminPages')]
    public function test_saving_an_untouched_form_keeps_the_page_exactly_as_the_original_copy(string $page, string $url): void
    {
        $before = $this->get($url)->assertOk()->getContent();
        $this->actingAs(User::factory()->create());

        Livewire::test($page)->assertOk()->call('save')->assertHasNoFormErrors();

        $this->assertDatabaseHas('page_sections', ['page' => SiteContent::PAGE]);
        $this->assertSame($this->visiblePage($before), $this->visiblePage($this->get($url)->getContent()));
    }

    public function test_texts_and_links_of_a_page_are_editable_per_language(): void
    {
        $this->actingAs(User::factory()->create());

        Livewire::test(ManageSocial::class)
            ->set('data.c0.es.title', 'Conversaciones que mueven marcas.')
            ->set('data.c0.es.ctaHref', 'https://wa.me/50222967668')
            ->set('data.c0.es.nextHref', '/casos')
            ->set('data.c0.en.title', 'Conversations that move brands.')
            ->set('data.c0.es.stickerAccent', '')
            ->call('save')
            ->assertHasNoFormErrors();

        $this->get('/social-media-strategies')
            ->assertSee('Conversaciones que mueven marcas.')
            ->assertSee('href="https://wa.me/50222967668"', false)
            ->assertSee('href="'.url('casos').'?lang=es&amp;market=gt"', false)
            ->assertSee('<em>talk.</em>', false)
            ->assertDontSee('No llenamos un feed.');
        $this->get('/social-media-strategies?lang=en')->assertSee('Conversations that move brands.');
    }

    public function test_unsafe_links_fall_back_to_the_automatic_destination(): void
    {
        app(SiteContent::class)->save('about', ['es' => ['closingHref' => 'javascript:alert(1)']]);

        $this->get('/nosotros')->assertDontSee('javascript:', false)->assertSee('class="about-contact-link" href="'.url('contacto').'?lang=es&amp;market=gt"', false);
    }

    public function test_footer_columns_keep_their_links_after_renaming_and_accept_custom_ones(): void
    {
        $footer = app(SiteContent::class)->defaults('global', 'es')['footer'];
        $footer['cols'][0]['head'] = 'Especialidades';
        $footer['cols'][2]['hrefs'][0] = 'mailto:hola@featbrands.com';
        app(SiteContent::class)->save('global', ['es' => ['footer' => $footer]]);

        $this->get('/')
            ->assertSee('Especialidades')
            ->assertSee('href="'.url('performance-connections').'?lang=es&amp;market=gt"', false)
            ->assertSee('<a href="mailto:hola@featbrands.com" style="font-size:15px', false);
    }

    public function test_home_specialties_awards_clients_and_offices_reach_every_page(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('home/proof/premio.webp', 'image');
        PageSection::create(['page' => 'home', 'section' => 'services', 'content_es' => ['cards' => [['title' => 'Media & Data']]]]);
        PageSection::create(['page' => 'home', 'section' => 'proof', 'content_es' => ['cards' => [['big' => '4×', 'label' => 'Effie', 'image' => 'home/proof/premio.webp', 'image_alt' => 'Trofeo Effie']]]]);
        PageSection::create(['page' => 'home', 'section' => 'collaboration', 'content_es' => ['clients' => [['name' => 'Marca Nueva']]]]);

        $this->get('/nosotros')
            ->assertSee('Media &amp; Data</a>', false)
            ->assertSee('<span>Marca Nueva</span>', false)
            ->assertSee('src="'.Storage::disk('public')->url('home/proof/premio.webp').'" alt="Trofeo Effie"', false);
        $this->get('/blog')->assertSee('Media &amp; Data</a>', false);
    }

    public function test_home_seo_settings_stay_on_the_home_page(): void
    {
        PageSection::create(['page' => 'home', 'section' => 'seo', 'content_es' => ['meta_title' => 'Título solo del home']]);

        $this->get('/')->assertSee('<title>Título solo del home</title>', false);
        $this->get('/nosotros')->assertDontSee('Título solo del home');
    }

    public function test_contact_messages_are_used_by_the_form_handler(): void
    {
        app(SiteContent::class)->save('contact', ['es' => ['success' => 'Gracias, te escribimos pronto.', 'referrals' => ['linkedin' => 'Perfil de LinkedIn']]]);

        $this->get('/contacto')->assertSee('Perfil de LinkedIn');
        $this->postJson('/contacto', [
            'first_name' => 'Ana', 'last_name' => 'Pérez', 'email' => 'ana@example.com', 'country_code' => '+502',
            'phone' => '5555-1234', 'company' => 'Marca', 'referral_source' => 'linkedin', 'locale' => 'es', 'market' => 'gt',
            'message' => 'Necesitamos una estrategia para el lanzamiento de nuestra marca.', 'website' => '',
        ])->assertCreated()->assertJson(['message' => 'Gracias, te escribimos pronto.']);
    }

    public function test_geo_pages_are_editable_but_keep_their_block_types(): void
    {
        $this->actingAs(User::factory()->create());
        $page = Livewire::test(ManageGeoPages::class)->set('data.c0.es.h1', '¿Qué agencia elegir en Guatemala?');
        $blocks = $page->get('data.c0.es.body');
        $first = array_key_first($blocks);
        $page->set("data.c0.es.body.{$first}.type", 'h2')->call('save')->assertHasNoFormErrors();

        $html = $this->get('/mejor-agencia-publicidad-guatemala')->assertOk()->getContent();
        $this->assertStringContainsString('<h1>¿Qué agencia elegir en Guatemala?</h1>', $html);
        $this->assertSame('p', app(SiteContent::class)->get('mejor-agencia-publicidad-guatemala', 'es')['body'][0]['type']);
        $this->get('/mejorar-comunicacion-marca-guatemala')->assertSee(File::json(resource_path('data/geo.json'))['pages']['mejorar-comunicacion-marca-guatemala']['h1']);
    }

    public function test_layout_logos_can_be_replaced(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('site/logos/feat.png', 'image');
        PageSection::create(['page' => SiteContent::PAGE, 'section' => SiteContent::MEDIA, 'content_es' => ['logo_light' => 'site/logos/feat.png']]);

        $html = $this->get('/')->getContent();

        $this->assertSame(2, substr_count($html, 'src="'.Storage::disk('public')->url('site/logos/feat.png').'"'));
        $this->assertStringContainsString('<link rel="icon" href="'.asset('assets/feat-black.svg').'">', $html);
    }

    public function test_saved_copy_keeps_the_original_structure(): void
    {
        $default = ['title' => 'Original', 'pairs' => [['a', 'b']], 'list' => ['x', 'y'], 'nested' => ['note' => 'n']];

        $merged = SiteContent::merge($default, ['title' => '  ', 'pairs' => [['c', ''], ['d', 'e']], 'list' => [], 'nested' => 'roto', 'extra' => 'ignorado']);

        $this->assertSame(['title' => 'Original', 'pairs' => [['c', 'b'], ['d', 'e']], 'list' => ['x', 'y'], 'nested' => ['note' => 'n']], $merged);
    }

    /**
     * Header, content and footer, without the per-request CSRF token.
     */
    private function visiblePage(string $html): string
    {
        $shell = substr($html, strpos($html, '<div class="site-shell"'), strpos($html, '<p class="site-language-status"') - strpos($html, '<div class="site-shell"'));

        return preg_replace('/name="_token" value="[^"]+"/', '', $shell);
    }
}
