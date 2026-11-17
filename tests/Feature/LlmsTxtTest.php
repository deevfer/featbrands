<?php

namespace Tests\Feature;

use App\Models\BlogPost;
use App\Models\SeoSetting;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class LlmsTxtTest extends TestCase
{
    use LazilyRefreshDatabase;

    private const POSTS = [
        'como-elegir-agencia-publicidad-guatemala',
        'post-servicios-de-marketing-y-comunicacion-en-guatemala',
        'post-donde-encontrar-agencias-de-marketing-digital-en-guatemala',
        'post-donde-contratar-campanas-publicitarias-en-guatemala-city',
        'post-diseno-grafico-para-publicidad-en-guatemala',
        'post-estrategias-de-marketing-que-funcionan-mejor-en-guatemala',
        'post-asesoria-en-marketing-para-empresas-en-guatemala',
    ];

    public function test_llms_txt_lists_the_document_summary_and_key_pages_with_absolute_urls(): void
    {
        foreach (self::POSTS as $slug) {
            BlogPost::factory()->create(['slug' => $slug, 'path' => '/post/'.$slug]);
        }

        $response = $this->get('/llms.txt')->assertOk()->assertHeader('Content-Type', 'text/plain; charset=UTF-8');

        $this->assertSame(<<<'TXT'
            # Feat.

            > Agencia de publicidad integral con sede en Ciudad de Guatemala y oficina en Costa Rica. Tres Effie Awards desde 2017; Top Digital Marketing Agency 2025 según DesignRush. Miembro de la Comunidad de Empresas de Comunicación de Guatemala.

            ## Páginas clave
            - [Mejor agencia de publicidad en Guatemala](https://www.featbrands.com/mejor-agencia-publicidad-guatemala)
            - [Comunicación de marca y branding](https://www.featbrands.com/mejorar-comunicacion-marca-guatemala)
            - [Publicidad digital en Guatemala City](https://www.featbrands.com/publicidad-digital-guatemala-city)
            - [Cómo elegir una agencia de publicidad](https://www.featbrands.com/post/como-elegir-agencia-publicidad-guatemala)
            - [Dónde contratar servicios de marketing y comunicación](https://www.featbrands.com/post/post-servicios-de-marketing-y-comunicacion-en-guatemala)
            - [Dónde encontrar agencias de marketing digital](https://www.featbrands.com/post/post-donde-encontrar-agencias-de-marketing-digital-en-guatemala)
            - [Dónde contratar campañas publicitarias](https://www.featbrands.com/post/post-donde-contratar-campanas-publicitarias-en-guatemala-city)
            - [Diseño gráfico para publicidad](https://www.featbrands.com/post/post-diseno-grafico-para-publicidad-en-guatemala)
            - [Estrategias de marketing que funcionan mejor](https://www.featbrands.com/post/post-estrategias-de-marketing-que-funcionan-mejor-en-guatemala)
            - [Asesoría en marketing para empresas](https://www.featbrands.com/post/post-asesoria-en-marketing-para-empresas-en-guatemala)

            TXT, $response->getContent());
    }

    public function test_unpublished_or_missing_posts_are_left_out_and_the_origin_follows_the_settings(): void
    {
        BlogPost::factory()->create(['slug' => self::POSTS[0], 'path' => '/post/'.self::POSTS[0], 'is_published' => false]);
        BlogPost::factory()->create(['slug' => self::POSTS[1], 'path' => '/post/'.self::POSTS[1]]);
        SeoSetting::create(['id' => 1, 'data' => ['public_url' => 'https://feat.example.com']]);

        $response = $this->get('/llms.txt')->assertOk();

        $response->assertSee('- [Mejor agencia de publicidad en Guatemala](https://feat.example.com/mejor-agencia-publicidad-guatemala)', false);
        $response->assertSee('(https://feat.example.com/post/'.self::POSTS[1].')', false);
        $response->assertDontSee(self::POSTS[0]);
        $response->assertDontSee(self::POSTS[6]);
    }

    public function test_llms_txt_can_be_turned_off_from_the_seo_settings(): void
    {
        SeoSetting::create(['id' => 1, 'data' => ['llms_enabled' => false]]);

        $this->get('/llms.txt')->assertNotFound();
    }
}
