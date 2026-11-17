<?php

namespace Tests\Feature;

use App\Models\SeoPage;
use App\Services\GeoContent;
use App\Services\SiteSeo;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\File;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\InspectsStructuredData;
use Tests\TestCase;

class GeoPagesTest extends TestCase
{
    use InspectsStructuredData, LazilyRefreshDatabase;

    /** @return array<string, array{string, int}> */
    public static function pages(): array
    {
        return [
            'mejor agencia' => ['mejor-agencia-publicidad-guatemala', 3],
            'comunicación de marca' => ['mejorar-comunicacion-marca-guatemala', 2],
            'publicidad digital' => ['publicidad-digital-guatemala-city', 2],
        ];
    }

    #[DataProvider('pages')]
    public function test_new_page_has_one_h1_self_canonical_spanish_only_metadata_and_schema(string $page, int $questions): void
    {
        $copy = File::json(resource_path('data/geo.json'))['pages'][$page];
        $seo = File::json(resource_path('data/seo.json'))['es'][$page];

        $xpath = $this->document($this->get('/'.$page.'?lang=en&market=cr')->assertOk()->getContent());

        $this->assertSame(1, $xpath->query('//h1')->length);
        $this->assertSame($copy['h1'], $xpath->evaluate('string(//h1)'));
        $this->assertSame(1, $xpath->query('//link[@rel="canonical"]')->length);
        $this->assertSame('https://www.featbrands.com/'.$page, $xpath->evaluate('string(//link[@rel="canonical"]/@href)'));
        $this->assertSame(0, $xpath->query('//link[@hreflang]')->length);
        $this->assertSame('es', $xpath->evaluate('string(/html/@lang)'));
        $this->assertSame($seo['title'], $xpath->evaluate('string(//title)'));
        $this->assertSame($seo['description'], $xpath->evaluate('string(//meta[@name="description"]/@content)'));
        $this->assertLessThanOrEqual(60, mb_strlen($seo['title']));
        $this->assertLessThanOrEqual(155, mb_strlen($seo['description']));

        $graph = $this->schemaGraph($xpath);
        $this->assertSame(['Organization', 'WebPage', 'FAQPage', 'BreadcrumbList'], array_column($graph, '@type'));
        $this->assertSame('https://www.featbrands.com/'.$page, collect($graph)->firstWhere('@type', 'WebPage')['url']);
        $this->assertSame($copy['faqs'], $this->assertVisibleFaqMatchesSchema($xpath, $questions));
        $this->assertSame(1, $xpath->query('//main//a[@href="/#servicios"][.="Nuestros servicios"]')->length);
    }

    #[DataProvider('pages')]
    public function test_new_page_renders_the_document_copy_verbatim(string $page): void
    {
        $xpath = $this->document($this->get('/'.$page)->assertOk()->getContent());
        $rendered = [];
        foreach ($xpath->query('//div[@class="blog-prose"]/*') as $node) {
            $rendered[] = $node->nodeName === 'ul'
                ? ['type' => 'ul', 'items' => array_map(fn ($item): string => $item->textContent, iterator_to_array($xpath->query('li', $node)))]
                : ['type' => $node->nodeName, 'text' => $node->textContent];
        }

        $expected = array_map(fn (array $block): array => $block['type'] === 'ul'
            ? ['type' => 'ul', 'items' => $block['items']]
            : ['type' => $block['type'], 'text' => $block['text']], File::json(resource_path('data/geo.json'))['pages'][$page]['body']);

        $this->assertSame($expected, $rendered);
        $this->assertSame(0, $xpath->query('//div[@class="blog-prose"]//h1|//div[@class="blog-prose"]//h3')->length);
    }

    public function test_body_links_point_to_the_posts_and_the_final_services_anchor(): void
    {
        $agency = $this->document($this->get('/mejor-agencia-publicidad-guatemala')->getContent());
        $this->assertSame('Criterios para elegir una agencia', $agency->evaluate('string(//h2/a[@href="/post/como-elegir-agencia-publicidad-guatemala"])'));

        $digital = $this->document($this->get('/publicidad-digital-guatemala-city')->getContent());
        $this->assertSame('dónde contratar campañas publicitarias en Guatemala City', $digital->evaluate('string(//p/a[@href="/post/post-donde-contratar-campanas-publicitarias-en-guatemala-city"])'));
        $this->assertSame(0, $digital->query('//a[contains(@href, "nuestrosservicios")]')->length);
    }

    public function test_new_pages_are_listed_only_in_the_spanish_sitemap(): void
    {
        $spanish = $this->get('/site-pages-sitemap.xml')->assertOk()->getContent();
        foreach (SiteSeo::GEO_PAGES as $page) {
            $this->assertStringContainsString('<loc>https://www.featbrands.com/'.$page.'</loc>', $spanish);
            $this->assertStringNotContainsString($page.'?lang=en', $spanish);
        }
    }

    public function test_seeding_page_faqs_is_idempotent_and_keeps_edits_from_filament(): void
    {
        $edited = [['question' => '¿Editada?', 'answer' => 'Sí.']];
        SeoPage::where('route_name', 'mejorar-comunicacion-marca-guatemala')->update(['faqs' => $edited]);

        app(GeoContent::class)->seedPageFaqs(app(SiteSeo::class));

        $this->assertSame($edited, SeoPage::where('route_name', 'mejorar-comunicacion-marca-guatemala')->first()->faqs);
        $this->assertSame(3, SeoPage::whereIn('route_name', SiteSeo::GEO_PAGES)->where('locale', 'es')->count());
        $this->assertSame(0, SeoPage::whereIn('route_name', SiteSeo::GEO_PAGES)->where('locale', 'en')->count());
    }
}
