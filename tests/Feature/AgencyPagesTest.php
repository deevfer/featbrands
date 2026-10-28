<?php

namespace Tests\Feature;

use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class AgencyPagesTest extends TestCase
{
    /**
     * @return array<string, array{string, string, string}>
     */
    public static function pages(): array
    {
        return [
            'cases' => ['cases', 'Ideas que', 'Ideas put'],
            'about' => ['about', 'El crecimiento', 'Growth happens'],
        ];
    }

    #[DataProvider('pages')]
    public function test_agency_page_renders_its_own_content_and_active_navigation(string $route, string $spanishTitle, string $englishTitle): void
    {
        $response = $this->get(route($route))
            ->assertOk()
            ->assertViewIs('site.'.$route)
            ->assertSeeText($spanishTitle);

        $this->assertMatchesRegularExpression(
            '/href="'.preg_quote(e(route($route, ['lang' => 'es', 'market' => 'gt'])), '/').'"\s+aria-current="page"/',
            $response->getContent(),
        );
    }

    #[DataProvider('pages')]
    public function test_english_page_preserves_its_route_and_market_in_navigation(string $route, string $spanishTitle, string $englishTitle): void
    {
        $response = $this->get(route($route, ['lang' => 'en', 'market' => 'cr']))
            ->assertOk()
            ->assertSeeText($englishTitle)
            ->assertSee('<html lang="en">', false)
            ->assertSee(route($route, ['lang' => 'es', 'market' => 'cr']))
            ->assertSee('data-market="cr"', false);

        foreach (['cases', 'about', 'makitha'] as $destination) {
            $response->assertSee(route($destination, ['lang' => 'en', 'market' => 'cr']));
        }
    }

    public function test_cases_keep_demonstration_labels_and_original_results(): void
    {
        $this->get('/casos')
            ->assertOk()
            ->assertSeeText('Ejemplos ilustrativos con datos demostrativos.')
            ->assertSeeText('Dato demostrativo')
            ->assertSeeText('+38%')
            ->assertSeeText('2.4×')
            ->assertSeeText('−27%')
            ->assertSeeText('El reto')
            ->assertSeeText('El trabajo')
            ->assertSeeText('El resultado')
            ->assertSee(route('social', ['lang' => 'es', 'market' => 'gt']));

        $this->get('/casos?lang=en')
            ->assertOk()
            ->assertSeeText('Illustrative examples with demonstration data.')
            ->assertSeeText('Demo data');
    }

    public function test_about_connects_specialties_and_regional_presence(): void
    {
        $this->get('/nosotros')
            ->assertOk()
            ->assertSeeText('La colaboración es nuestra forma de trabajar.')
            ->assertSeeText('Tecnología para Marketing y Ventas: web, apps, integraciones, data, growth e IA aplicada al negocio.')
            ->assertSeeText('Guatemala')
            ->assertSeeText('San José')
            ->assertSeeText('Miami')
            ->assertSee('id="reconocimientos"', false)
            ->assertSee('data-office="cr"', false);
    }

    public function test_home_connects_to_agency_pages_and_recognition(): void
    {
        $query = ['lang' => 'en', 'market' => 'us'];

        $this->get(route('home', $query))
            ->assertOk()
            ->assertSeeText('Explore work')
            ->assertSeeText('Meet Feat.')
            ->assertSee(route('cases', $query))
            ->assertSee(route('about', $query))
            ->assertSee(route('about', $query).'#reconocimientos');
    }
}
