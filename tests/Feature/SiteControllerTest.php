<?php

namespace Tests\Feature;

use App\Models\PageSection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SiteControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_renders_the_prototype_and_links_to_the_separate_performance_page(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertViewIs('site.home')
            ->assertSeeText('Entender, conectar, agregar valor,')
            ->assertSee(route('performance', ['lang' => 'es', 'market' => 'gt']))
            ->assertSee(asset('uploads/proof-bg-0.webp'))
            ->assertDontSee('data-dc-script');
    }

    public function test_performance_can_be_opened_directly_without_the_home_sections(): void
    {
        $this->get('/performance-connections')
            ->assertOk()
            ->assertViewIs('site.performance')
            ->assertSeeText('Inversión en medios conectada a resultados de negocio.')
            ->assertSeeText('Preguntas frecuentes')
            ->assertDontSee('data-deck-card');
    }

    public function test_language_switch_keeps_the_current_page_and_market(): void
    {
        $this->get('/performance-connections?lang=en&market=cr')
            ->assertOk()
            ->assertSee('<html lang="en">', false)
            ->assertSeeText('Media investment connected to business results.')
            ->assertSee(route('performance', ['lang' => 'es', 'market' => 'cr']))
            ->assertSee('data-market="cr"', false);
    }

    public function test_invalid_language_and_market_fall_back_to_the_original_defaults(): void
    {
        $this->get('/?lang[]=en&market[]=cr')
            ->assertOk()
            ->assertSee('<html lang="es">', false)
            ->assertSee('data-market="gt"', false);
    }

    public function test_filament_login_is_available_and_dashboard_requires_authentication(): void
    {
        $this->get('/admin/login')->assertOk();
        $this->get('/admin')->assertRedirect('/admin/login');
    }

    public function test_home_embeds_the_youtube_reel_saved_in_the_cms(): void
    {
        $this->get('/')->assertOk()->assertDontSee('youtube-nocookie.com', false)->assertDontSee('home-reel has-video', false);

        $media = ['video_file' => null, 'video_url' => 'https://www.youtube.com/watch?v=52N7Q5XoT-I&authuser=0', 'poster' => null];
        PageSection::create(['page' => 'home', 'section' => 'cases', 'content_es' => ['media' => $media], 'content_en' => ['media' => $media]]);

        foreach (['/', '/?lang=en'] as $url) {
            $this->get($url)
                ->assertOk()
                ->assertSee('src="https://www.youtube-nocookie.com/embed/52N7Q5XoT-I"', false)
                ->assertSee('home-reel has-video', false);
        }
    }

    public function test_home_shows_the_client_logos_in_slides_of_eight(): void
    {
        $response = $this->get('/')->assertOk();
        $html = $response->getContent();

        $this->assertSame(6, substr_count($html, 'data-client-slide '));
        $this->assertSame(6, substr_count($html, 'data-client-dot='));
        $this->assertSame(44, substr_count($html, 'class="client-logo"'));
        $response->assertSee('src="'.asset('assets/client-logos/tigo.webp').'" alt="Logotipo de Tigo"', false);
        $response->assertSee('alt="Logotipo de Nivea" loading="lazy" decoding="async" style="height:100%"', false);
        $response->assertSee('alt="Logotipo de Beiersdorf" loading="lazy" decoding="async" style="height:41%"', false);

        $this->get('/?lang=en')->assertSee('alt="Tigo logo"', false);
    }

    public function test_hero_deck_cards_use_a_solid_color_or_an_image_from_the_cms(): void
    {
        $this->get('/')->assertSee('class="deck-card" data-deck-card="0"', false)
            ->assertSee('style="background-color:#FB6C26;color:#000000"', false);

        Storage::fake('public');
        Storage::disk('public')->put('home/deck/performance.webp', 'image');
        $styles = [
            ['background' => '#111111', 'foreground' => '#FFFFFF', 'image' => 'home/deck/performance.webp', 'overlay' => 40],
            ['background' => 'red;position:fixed', 'foreground' => '#FFFFFF', 'image' => null, 'overlay' => 0],
        ];
        PageSection::create(['page' => 'home', 'section' => 'services', 'content_es' => ['card_styles' => $styles], 'content_en' => ['card_styles' => $styles]]);

        $this->get('/')
            ->assertSee('class="deck-card has-image" data-deck-card="0"', false)
            ->assertSee("background-color:#111111;color:#FFFFFF;--deck-image:url('".Storage::disk('public')->url('home/deck/performance.webp')."');--deck-overlay:0.4", false)
            ->assertSee('class="deck-card" data-deck-card="1" data-service-url', false)
            ->assertSee('style="background-color:#2D48F2;color:#FFFFFF"', false)
            ->assertDontSee('position:fixed', false);
    }

    public function test_about_scrolls_every_client_logo_in_an_endless_row(): void
    {
        $html = $this->get('/nosotros')->assertOk()->getContent();

        $this->assertSame(2, substr_count($html, 'class="about-brand-names"'));
        $this->assertSame(1, substr_count($html, 'alt="Logotipo de Coca-Cola"'));
        $this->assertSame(88, substr_count($html, 'src="'.asset('assets/client-logos/')));
        $this->assertMatchesRegularExpression('/<ul class="about-brand-names"\s+aria-hidden="true"/', $html);
    }
}
