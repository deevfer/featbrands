<?php

namespace Tests\Feature;

use App\Filament\Resources\BlogPosts\Pages\EditBlogPost;
use App\Models\BlogPost;
use App\Models\User;
use App\Services\BlogImporter;
use DOMDocument;
use DOMXPath;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\File;
use Livewire\Livewire;
use Tests\TestCase;

class BlogTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_all_original_urls_preserve_article_text_titles_and_dates(): void
    {
        app(BlogImporter::class)->import();
        $catalog = File::json(resource_path('data/blog/catalog.json'));
        $this->assertDatabaseCount('blog_posts', 214);
        $this->assertDatabaseCount('blog_categories', 5);

        foreach ($catalog['posts'] as $article) {
            $path = implode('/', array_map('rawurlencode', explode('/', $article['path'])));
            $response = $this->get($path)->assertOk()->assertSeeText($article['title']);
            $document = new DOMDocument;
            $previous = libxml_use_internal_errors(true);
            $document->loadHTML('<?xml encoding="UTF-8">'.$response->getContent(), LIBXML_NONET);
            libxml_clear_errors();
            libxml_use_internal_errors($previous);
            $root = (new DOMXPath($document))->query('//*[@data-blog-content]')->item(0);
            $this->assertNotNull($root, $article['path']);
            $compact = preg_replace('/[\s\p{Z}\x{FEFF}\x{200B}]+/u', '', $root->textContent);
            $this->assertSame($article['source_text_sha256'], hash('sha256', $compact), $article['path']);
            $post = BlogPost::where('path', $article['path'])->firstOrFail();
            $this->assertSame($article['author'], $post->author);
            $this->assertSame(substr($article['published_at'], 0, 10), $post->published_at->toDateString());
        }
    }

    public function test_index_categories_search_and_pagination_work_at_original_paths(): void
    {
        app(BlogImporter::class)->import();
        $this->get('/blog')->assertOk()->assertSeeText('211 artículos')->assertSeeText('Otra mirada');
        $this->get('/blog/page/2')->assertOk()->assertSeeText('Página 2 de');
        $this->get('/blog/categories/nuestra-sabidur%C3%ADa')->assertOk()->assertSeeText('89 artículos');
        $this->get('/blog/categories/back2basics')->assertOk()->assertSeeText('Todavía no hay artículos aquí.');
        $this->get('/blog/search/branding')->assertOk()->assertSeeText('Resultados para')->assertSeeText('branding');
        $this->get('/blog/tags/advertising')->assertOk()->assertSeeText('Las tendencias son tendencia.');
        $this->get('/blog/categories/no-existe')->assertNotFound();
        $this->get('/blog/page/9999')->assertNotFound();
        $this->get('/post/no-existe')->assertNotFound();
    }

    public function test_original_english_versions_and_actual_translation_links_are_preserved(): void
    {
        app(BlogImporter::class)->import();
        $this->get('/en/blog')->assertOk()->assertSee('<html lang="en">', false)->assertSeeText('3 articles');
        $this->get('/en/post/2024-broken-expectations-surprises-fulfilled')->assertOk()
            ->assertSeeText('2024: Broken expectations, surprises fulfilled')
            ->assertSee('/post/2024-expectativas-rotas-sorpresas-cumplidas?market=gt');
        $this->get('/post/como-elegir-agencia-publicidad-guatemala?lang=en')->assertOk()
            ->assertSeeText('This article is available in Spanish.')
            ->assertSeeText('Cómo Elegir una Agencia de Publicidad en Guatemala: Guía de Criterios');
    }

    public function test_import_is_repeatable_and_preserves_cms_edits(): void
    {
        $importer = app(BlogImporter::class);
        $importer->import();
        $post = BlogPost::firstOrFail();
        $post->update(['title' => 'Título editado en el CMS']);
        $result = $importer->import();
        $this->assertSame(0, $result['created']);
        $this->assertSame(214, $result['skipped']);
        $this->assertDatabaseCount('blog_posts', 214);
        $this->assertSame('Título editado en el CMS', $post->fresh()->title);
    }

    public function test_draft_and_scheduled_posts_are_not_public(): void
    {
        $draft = BlogPost::factory()->create(['title' => 'Borrador secreto', 'is_published' => false]);
        $future = BlogPost::factory()->create(['title' => 'Artículo futuro', 'published_at' => now()->addDay()]);
        $this->get('/blog')->assertOk()->assertDontSeeText('Borrador secreto')->assertDontSeeText('Artículo futuro');
        $this->get($draft->path)->assertNotFound();
        $this->get($future->path)->assertNotFound();
    }

    public function test_post_content_and_metadata_are_escaped_and_sanitized(): void
    {
        $post = BlogPost::factory()->create(['title' => '<script>alert("title")</script>', 'content' => '<p>Contenido seguro</p><script>alert("body")</script><img src="/photo.png" onerror="alert(1)"><a href="javascript:alert(1)">Enlace</a>']);
        $this->get($post->path)->assertOk()->assertSeeText('Contenido seguro')
            ->assertDontSee('<script>alert("title")</script>', false)
            ->assertDontSee('<script>alert("body")</script>', false)
            ->assertDontSee('onerror=', false)->assertDontSee('href="javascript:', false);
    }

    public function test_article_and_category_administration_require_authentication(): void
    {
        $this->get('/admin/blog-posts')->assertRedirect('/admin/login');
        $this->get('/admin/blog-categories')->assertRedirect('/admin/login');
        config(['app.env' => 'local']);
        $this->actingAs(User::factory()->create());
        $post = BlogPost::factory()->create();
        $this->get('/admin/blog-posts')->assertOk()->assertSeeText($post->title);
        $this->get('/admin/blog-categories')->assertOk();
        Livewire::test(EditBlogPost::class, ['record' => $post->getRouteKey()])
            ->fillForm(['title' => 'Artículo editado', 'author' => 'Autor original'])
            ->call('save')->assertHasNoFormErrors();
        $this->assertSame('Artículo editado', $post->fresh()->title);
        $this->assertSame($post->path, $post->fresh()->path);
        $this->assertStringContainsString('Contenido del artículo de prueba.', $post->fresh()->content);
    }

    public function test_imported_images_are_bundled_locally(): void
    {
        $catalog = File::json(resource_path('data/blog/catalog.json'));
        $paths = [];
        foreach ($catalog['posts'] as $post) {
            if ($post['cover_image']) {
                $paths[] = $post['cover_image'];
            }
            preg_match_all('~<img[^>]+src="([^"]+)"~', $post['content'], $matches);
            $paths = [...$paths, ...$matches[1]];
        }
        foreach (array_unique($paths) as $path) {
            $this->assertStringStartsWith('/uploads/blog/', $path);
            $this->assertFileExists(public_path(ltrim($path, '/')));
        }
    }
}
