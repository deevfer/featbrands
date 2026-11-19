<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class SpecializationPagesTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array<string, array{string, string, string}>
     */
    public static function specializations(): array
    {
        return [
            'brand' => ['brand', 'Estrategia de marca', 'A brand is not explained.'],
            'social' => ['social', 'La conversación diaria de tu marca,', 'We do not fill a feed.'],
            'experience' => ['experience', 'Experiencias de marca', 'Make it feel easy.'],
            'makitha' => ['makitha', 'Tecnología que ayuda a tu marca', 'We make technology'],
        ];
    }

    #[DataProvider('specializations')]
    public function test_specialization_has_its_own_page_and_content(string $route, string $spanishTitle, string $englishTitle): void
    {
        $this->get(route($route))
            ->assertOk()
            ->assertViewIs('site.'.$route)
            ->assertSeeText($spanishTitle)
            ->assertDontSeeText('Inversión en medios conectada a resultados de negocio.')
            ->assertSee('aria-current="page"', false);
    }

    #[DataProvider('specializations')]
    public function test_specialization_has_english_content_and_keeps_its_route_when_switching_language(string $route, string $spanishTitle, string $englishTitle): void
    {
        $this->get(route($route, ['lang' => 'en', 'market' => 'us']))
            ->assertOk()
            ->assertSeeText($englishTitle)
            ->assertSee('<html lang="en">', false)
            ->assertSee(route($route, ['lang' => 'es', 'market' => 'us']))
            ->assertSee('data-market="us"', false);
    }

    public function test_home_links_to_all_five_specializations(): void
    {
        $response = $this->get('/?lang=en&market=cr')->assertOk();

        foreach (['performance', 'brand', 'social', 'experience', 'makitha'] as $route) {
            $response->assertSee(route($route, ['lang' => 'en', 'market' => 'cr']));
        }
    }

    public function test_makitha_technology_positioning_is_consistent_across_the_site(): void
    {
        $response = $this->get('/makitha')->assertOk();

        foreach (['Intelligence for scalable growth', 'Páginas Web y Landing Pages', 'Apps SaaS & Web', 'Estrategias de Growth', 'Integración de Herramientas', 'Data & Dashboards', 'IA Aplicada al Negocio'] as $offering) {
            $response->assertSeeText($offering);
        }

        $response->assertDontSeeText('Producción audiovisual')
            ->assertDontSee('studio-slate')
            ->assertSee('https://www.makitha.com/?lang=es');

        $this->get('/')
            ->assertOk()
            ->assertSeeText('Desarrollo tecnológico para vender, atender y ordenar la operación de tu marca.')
            ->assertDontSeeText('Producción ágil, animación 2D/3D, audio y estudio audiovisual.');

        $this->get('/social-media-strategies')
            ->assertOk()
            ->assertSeeText('Conecta contenido, herramientas y datos')
            ->assertDontSeeText('Cuando la idea necesita producción');

        $this->get('/makitha?lang=en')
            ->assertOk()
            ->assertSeeText('AI Applied to Business')
            ->assertDontSeeText('Film production');
    }
}
