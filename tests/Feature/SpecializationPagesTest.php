<?php

namespace Tests\Feature;

use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class SpecializationPagesTest extends TestCase
{
    /**
     * @return array<string, array{string, string, string}>
     */
    public static function specializations(): array
    {
        return [
            'brand' => ['brand', 'Una marca no se explica.', 'A brand is not explained.'],
            'social' => ['social', 'No llenamos un feed.', 'We do not fill a feed.'],
            'experience' => ['experience', 'Que se sienta fácil.', 'Make it feel easy.'],
            'makitha' => ['makitha', 'Hacemos la tecnología', 'We make technology'],
        ];
    }

    #[DataProvider('specializations')]
    public function test_specialization_has_its_own_page_and_content(string $route, string $spanishTitle, string $englishTitle): void
    {
        $this->get(route($route))
            ->assertOk()
            ->assertViewIs('site.'.$route)
            ->assertSeeText($spanishTitle)
            ->assertDontSeeText('Growth Marketing en Guatemala que se mide en negocio, no en likes.')
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

        foreach (['Tech Growth Partner', 'Páginas Web y Landing Pages', 'Apps SaaS & Web', 'Estrategias de Growth', 'Integración de Herramientas', 'Data & Dashboards', 'IA Aplicada al Negocio'] as $offering) {
            $response->assertSeeText($offering);
        }

        $response->assertDontSeeText('Producción audiovisual')
            ->assertDontSee('studio-slate')
            ->assertSee('https://www.makitha.com/?lang=es');

        $this->get('/')
            ->assertOk()
            ->assertSeeText('Tecnología para Marketing y Ventas: web, apps, integraciones, data, growth e IA aplicada al negocio.')
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
