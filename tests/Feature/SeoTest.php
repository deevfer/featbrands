<?php

namespace Tests\Feature;

use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\SeoPage;
use App\Models\SeoSetting;
use App\Services\BlogImporter;
use App\Services\SiteSeo;
use DOMDocument;
use DOMXPath;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class SeoTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_service_metadata_has_canonical_languages_and_truthful_schema_without_market_duplicates(): void
    {
        $response = $this->get('/makitha?lang=en&market=cr')->assertOk();
        $xpath = $this->document($response->getContent());
        $this->assertSame('https://www.featbrands.com/makitha?lang=en', $xpath->evaluate('string(//link[@rel="canonical"]/@href)'));
        $this->assertSame(1, $xpath->query('//link[@rel="canonical"]')->length);
        $this->assertSame('https://www.featbrands.com/makitha', $xpath->evaluate('string(//link[@hreflang="es"]/@href)'));
        $this->assertSame('https://www.featbrands.com/makitha?lang=en', $xpath->evaluate('string(//link[@hreflang="en"]/@href)'));
        $schema = json_decode($xpath->evaluate('string(//script[@type="application/ld+json"])'), true);
        $service = collect($schema['@graph'])->firstWhere('@type', 'Service');
        $this->assertSame('Makitha', explode(' — ', $service['name'])[0]);
        $this->assertStringContainsString('technology', $service['description']);
        $this->assertArrayNotHasKey('aggregateRating', $service);
        $this->assertStringContainsString('noindex', $xpath->evaluate('string(//meta[@name="robots"]/@content)'));
    }

    public function test_local_previews_are_noindex_but_production_allows_search_crawlers(): void
    {
        $this->get('/robots.txt')->assertOk()->assertHeader('Content-Type', 'text/plain; charset=UTF-8')->assertSee("Disallow: /\n", false);
        $this->get('/')->assertHeader('X-Robots-Tag', 'noindex, follow');
        $this->app->instance('env', 'production');
        $this->get('/robots.txt')->assertOk()->assertSee('User-agent: OAI-SearchBot', false)->assertSee('User-agent: PerplexityBot', false)->assertSee('User-agent: Googlebot', false)->assertSee('Disallow: /admin', false)->assertSee('Sitemap: https://www.featbrands.com/sitemap.xml', false);
        $this->get('/')->assertOk()->assertHeader('X-Robots-Tag', 'index, follow, max-image-preview:large');
        SeoSetting::create(['id' => 1, 'data' => ['indexing_enabled' => false]]);
        $this->get('/robots.txt')->assertSee("Disallow: /\n", false);
        $this->get('/')->assertHeader('X-Robots-Tag', 'noindex, follow');
    }

    public function test_private_routes_errors_and_search_results_stay_out_of_indexes(): void
    {
        $this->app->instance('env', 'production');
        $this->get('/admin/login')->assertOk()->assertHeader('X-Robots-Tag', 'noindex, nofollow');
        $this->get('/no-existe')->assertNotFound()->assertHeader('X-Robots-Tag', 'noindex, nofollow');
        $this->get('/blog/search/branding')->assertOk()->assertHeader('X-Robots-Tag', 'noindex, follow');
        BlogCategory::factory()->create(['slug' => 'sin-articulos']);
        $this->get('/blog/categories/sin-articulos')->assertOk()->assertHeader('X-Robots-Tag', 'noindex, follow');
    }

    public function test_sitemaps_preserve_original_unicode_urls_and_exclude_unpublished_articles(): void
    {
        $this->freezeTime();
        $post = BlogPost::factory()->create(['slug' => 'estrategia-y-conexión', 'original_updated_at' => now()->subDay()]);
        $english = BlogPost::factory()->create(['locale' => 'en', 'slug' => 'strategy-and-connections']);
        $draft = BlogPost::factory()->create(['is_published' => false]);
        $scheduled = BlogPost::factory()->create(['published_at' => now()->addDay()]);
        $index = $this->get('/sitemap.xml')->assertOk()->assertHeader('Content-Type', 'application/xml; charset=UTF-8');
        $xml = new DOMDocument;
        $this->assertTrue($xml->loadXML($index->getContent()));
        $this->assertCount(5, $xml->getElementsByTagName('sitemap'));
        $posts = $this->get('/blog-posts-sitemap.xml')->assertOk()->assertSee('estrategia-y-conexi%C3%B3n', false)->assertDontSee($draft->slug)->assertDontSee($scheduled->slug)->assertDontSee($english->slug);
        $this->assertTrue($xml->loadXML($posts->getContent()));
        $this->assertSame(1, $xml->getElementsByTagName('url')->length);
        $this->assertSame($post->original_updated_at->toIso8601String(), $xml->getElementsByTagName('lastmod')->item(0)->textContent);
        $this->get('/en_blog-posts-sitemap.xml')->assertOk()->assertSee($english->slug);
        $this->get('/site-pages-sitemap.xml')->assertOk()->assertSee('https://www.featbrands.com/makitha?lang=en', false)->assertDontSee('market=');
        $this->get('/pages-sitemap.xml')->assertOk();
    }

    public function test_only_nonempty_categories_appear_in_category_sitemaps(): void
    {
        $category = BlogCategory::factory()->create(['slug' => 'sabiduría']);
        BlogCategory::factory()->create(['slug' => 'vacia']);
        $post = BlogPost::factory()->create();
        $post->categories()->attach($category);
        $this->get('/blog-categories-sitemap.xml')->assertOk()->assertSee('sabidur%C3%ADa', false)->assertDontSee('/vacia');
    }

    public function test_cms_seo_overrides_are_rendered_safely_and_visible_content_is_preserved(): void
    {
        SeoPage::factory()->create(['route_name' => 'makitha', 'title' => 'Tecnología para tu negocio | Feat.', 'description' => 'Explora nuestras soluciones tecnológicas e integraciones con Makitha, la unidad de tecnología de Feat.']);
        $response = $this->get('/makitha')->assertOk()->assertSee('<title>Tecnología para tu negocio | Feat.</title>', false);
        $response->assertSeeText('Makitha');
        SeoPage::where('route_name', 'makitha')->update(['title' => '<script>alert("seo")</script>', 'description' => 'Texto </script><script>alert("schema")</script>']);
        $this->get('/makitha')->assertOk()->assertDontSee('<script>alert("seo")</script>', false)->assertDontSee('<script>alert("schema")</script>', false);
    }

    public function test_real_translations_use_original_canonical_urls_and_fallback_ui_is_noindex(): void
    {
        app(BlogImporter::class)->import();
        $this->app->instance('env', 'production');
        $response = $this->get('/post/2024-expectativas-rotas-sorpresas-cumplidas?market=cr')->assertOk();
        $xpath = $this->document($response->getContent());
        $this->assertSame('https://www.featbrands.com/en/post/2024-broken-expectations-surprises-fulfilled', $xpath->evaluate('string(//link[@hreflang="en"]/@href)'));
        $fallback = $this->get('/post/como-elegir-agencia-publicidad-guatemala?lang=en')->assertOk()->assertHeader('X-Robots-Tag', 'noindex, follow');
        $xpath = $this->document($fallback->getContent());
        $this->assertSame(0, $xpath->query('//link[@hreflang="en"]')->length);
        $this->assertSame('https://www.featbrands.com/post/como-elegir-agencia-publicidad-guatemala', $xpath->evaluate('string(//link[@rel="canonical"]/@href)'));
    }

    public function test_editing_an_article_updates_lastmod_without_rewriting_its_original_body(): void
    {
        $this->freezeTime();
        $post = BlogPost::factory()->create(['original_updated_at' => now()->subYear()]);
        $content = $post->content;
        $post->update(['seo_description' => 'Descripción actualizada para mostrar correctamente el artículo en los buscadores y en las tarjetas para compartir.']);
        $this->assertSame($content, $post->fresh()->content);
        $this->assertSame(now()->toIso8601String(), $post->fresh()->original_updated_at->toIso8601String());
        $this->get('/blog-posts-sitemap.xml')->assertSee(now()->toIso8601String(), false);
    }

    public function test_project_seo_setup_is_repeatable_and_preserves_editorial_changes(): void
    {
        $seo = app(SiteSeo::class);
        $seo->initialize();
        $this->assertDatabaseCount('seo_pages', 20);
        SeoPage::where('route_name', 'home')->where('locale', 'es')->update(['title' => 'Nuestro título actualizado']);
        $seo->initialize();
        $this->assertDatabaseCount('seo_pages', 20);
        $this->get('/')->assertSee('<title>Nuestro título actualizado</title>', false);
    }

    public function test_long_original_titles_keep_distinctive_endings_without_changing_visible_headlines(): void
    {
        $first = BlogPost::factory()->create(['title' => 'Cómo las agencias de publicidad en Guatemala, Costa Rica y Miami transforman el marketing digital', 'seo_title' => null]);
        $second = BlogPost::factory()->create(['title' => 'Cómo las agencias de publicidad en Guatemala, Costa Rica y Miami impulsan el marketing digital', 'seo_title' => null]);
        $firstResponse = $this->get($first->path)->assertOk()->assertSeeText($first->title);
        $secondResponse = $this->get($second->path)->assertOk()->assertSeeText($second->title);
        $titleA = $this->document($firstResponse->getContent())->evaluate('string(//title)');
        $titleB = $this->document($secondResponse->getContent())->evaluate('string(//title)');
        $this->assertNotSame($titleA, $titleB);
        $this->assertStringContainsString('transforman', $titleA);
        $this->assertStringContainsString('impulsan', $titleB);
        $this->assertLessThanOrEqual(70, mb_strlen($titleA));
    }

    public function test_article_images_on_the_public_disk_use_a_consistent_sharing_url(): void
    {
        $post = BlogPost::factory()->create(['cover_image' => 'blog/portada.jpg']);
        $response = $this->get($post->path)->assertOk();
        $this->assertSame('https://www.featbrands.com/storage/blog/portada.jpg', $this->document($response->getContent())->evaluate('string(//meta[@property="og:image"]/@content)'));
    }

    private function document(string $html): DOMXPath
    {
        $document = new DOMDocument;
        $previous = libxml_use_internal_errors(true);
        $document->loadHTML('<?xml encoding="UTF-8">'.$html, LIBXML_NONET);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        return new DOMXPath($document);
    }
}
