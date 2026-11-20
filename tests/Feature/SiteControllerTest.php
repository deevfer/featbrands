<?php

namespace Tests\Feature;

use App\Models\PageSection;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
}
