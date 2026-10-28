<?php

namespace Tests\Feature;

use Tests\TestCase;

class SiteControllerTest extends TestCase
{
    public function test_home_renders_the_prototype_and_links_to_the_separate_performance_page(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertViewIs('site.home')
            ->assertSeeText('Combinamos especializaciones para acelerar tu')
            ->assertSee(route('performance', ['lang' => 'es', 'market' => 'gt']))
            ->assertSee(asset('uploads/proof-bg-0.webp'))
            ->assertDontSee('data-dc-script');
    }

    public function test_performance_can_be_opened_directly_without_the_home_sections(): void
    {
        $this->get('/performance-connections')
            ->assertOk()
            ->assertViewIs('site.performance')
            ->assertSeeText('Growth Marketing en Guatemala que se mide en negocio, no en likes.')
            ->assertSeeText('Preguntas frecuentes')
            ->assertDontSee('data-deck-card');
    }

    public function test_language_switch_keeps_the_current_page_and_market(): void
    {
        $this->get('/performance-connections?lang=en&market=cr')
            ->assertOk()
            ->assertSee('<html lang="en">', false)
            ->assertSeeText('Growth marketing measured in business, not likes.')
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
}
