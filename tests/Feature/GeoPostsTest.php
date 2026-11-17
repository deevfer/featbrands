<?php

namespace Tests\Feature;

use App\Models\BlogPost;
use App\Services\GeoContent;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\File;
use PHPUnit\Framework\Attributes\DataProvider;
use RuntimeException;
use Tests\InspectsStructuredData;
use Tests\TestCase;

class GeoPostsTest extends TestCase
{
    use InspectsStructuredData, LazilyRefreshDatabase;

    private const CAMPAIGN = 'cómo-elaborar-una-campaña-de-publicidad-exitosa-claves-y-estrategias';

    private const PLANNING = 'cómo-planificar-una-campaña-de-publicidad-exitosa-el-papel-de-una-agencia-de-publicidad-en-las-rela';

    private const CAMPAIGN_CONTENT = '<p>Una <strong>campaña de publicidad</strong> es una herramienta fundamental.</p><br><h3><strong>¿Qué es una Campaña de Publicidad?</strong></h3><p>Resto del artículo.</p>';

    /** @return array<string, array{string, int}> */
    public static function posts(): array
    {
        return [
            'diseño gráfico' => ['post-diseno-grafico-para-publicidad-en-guatemala', 2],
            'servicios de marketing' => ['post-servicios-de-marketing-y-comunicacion-en-guatemala', 3],
            'estrategias' => ['post-estrategias-de-marketing-que-funcionan-mejor-en-guatemala', 2],
            'cómo elegir agencia' => ['como-elegir-agencia-publicidad-guatemala', 2],
            'campaña exitosa' => [self::CAMPAIGN, 1],
            'agencias de marketing digital' => ['post-donde-encontrar-agencias-de-marketing-digital-en-guatemala', 1],
        ];
    }

    #[DataProvider('posts')]
    public function test_post_gets_one_faq_page_identical_to_its_visible_questions(string $slug, int $questions): void
    {
        $this->createPosts();
        app(GeoContent::class)->applyToPosts();

        $xpath = $this->document($this->get('/post/'.rawurlencode($slug))->assertOk()->getContent());

        $this->assertSame(File::json(resource_path('data/geo.json'))['posts'][$slug]['faqs'], $this->assertVisibleFaqMatchesSchema($xpath, $questions));
        $this->assertSame(1, $xpath->query('//h1')->length);
        $this->assertSame('https://www.featbrands.com/post/'.rawurlencode($slug), $xpath->evaluate('string(//link[@rel="canonical"]/@href)'));
    }

    public function test_campaign_post_gets_the_block_after_its_introduction_and_a_new_modification_date(): void
    {
        $this->createPosts();
        $this->travelTo(now()->addDay());

        app(GeoContent::class)->applyToPosts();

        $block = File::json(resource_path('data/geo.json'))['posts'][self::CAMPAIGN]['insert_after_intro'];
        $post = BlogPost::where('slug', self::CAMPAIGN)->first();
        $this->assertStringStartsWith('<p>Una <strong>campaña de publicidad</strong> es una herramienta fundamental.</p><p>'.$block['intro'].'</p>', $post->content);
        $this->assertStringEndsWith('<br><h3><strong>¿Qué es una Campaña de Publicidad?</strong></h3><p>Resto del artículo.</p>', $post->content);
        $this->assertStringContainsString('<a href="/post/'.rawurlencode(self::PLANNING).'">'.$block['link']['text'].'</a>', $post->content);
        $this->assertSame(now()->toDateTimeString(), $post->original_updated_at->toDateTimeString());

        $xpath = $this->document($this->get('/post/'.self::CAMPAIGN)->assertOk()->getContent());
        $prose = $xpath->query('//div[@data-blog-content]/p');
        $this->assertSame([$block['intro'], ...$block['steps'], $block['link']['text']], array_map(fn ($node): string => $node->textContent, array_slice(iterator_to_array($prose), 1, 5)));
        $posting = collect($this->schemaGraph($xpath))->firstWhere('@type', 'BlogPosting');
        $this->assertSame($post->original_updated_at->toIso8601String(), $posting['dateModified']);
    }

    public function test_applying_the_content_twice_changes_nothing(): void
    {
        $this->createPosts();
        app(GeoContent::class)->applyToPosts();
        $before = BlogPost::orderBy('id')->get(['slug', 'content', 'faqs', 'original_updated_at'])->toArray();
        $this->travelTo(now()->addWeek());

        app(GeoContent::class)->applyToPosts();

        $this->assertSame($before, BlogPost::orderBy('id')->get(['slug', 'content', 'faqs', 'original_updated_at'])->toArray());
    }

    public function test_posts_outside_the_document_are_not_touched(): void
    {
        $this->createPosts();
        $untouched = BlogPost::factory()->create(['slug' => 'post-asesoria-en-marketing-para-empresas-en-guatemala', 'path' => '/post/post-asesoria-en-marketing-para-empresas-en-guatemala']);

        app(GeoContent::class)->applyToPosts();

        $this->assertNull($untouched->fresh()->faqs);
        $this->assertSame($untouched->content, $untouched->fresh()->content);
    }

    public function test_a_missing_post_fails_clearly_and_rolls_back(): void
    {
        $this->createPosts();
        BlogPost::where('slug', 'como-elegir-agencia-publicidad-guatemala')->delete();

        try {
            app(GeoContent::class)->applyToPosts();
            $this->fail('A missing post must stop the content migration.');
        } catch (RuntimeException $exception) {
            $this->assertStringContainsString('como-elegir-agencia-publicidad-guatemala', $exception->getMessage());
        }

        $this->assertSame(0, BlogPost::whereNotNull('faqs')->count());
    }

    public function test_campaign_link_stays_as_plain_text_when_the_planning_post_does_not_exist(): void
    {
        $this->createPosts();
        BlogPost::where('slug', self::PLANNING)->delete();

        app(GeoContent::class)->applyToPosts();

        $content = BlogPost::where('slug', self::CAMPAIGN)->value('content');
        $this->assertStringContainsString('<p>Cómo planificar una campaña de publicidad exitosa</p>', $content);
        $this->assertStringNotContainsString('planificar-una', $content);
    }

    private function createPosts(): void
    {
        foreach (array_column(self::posts(), 0) as $slug) {
            BlogPost::factory()->create(['slug' => $slug, 'path' => '/post/'.$slug, 'original_updated_at' => now()->subYear(), 'content' => $slug === self::CAMPAIGN ? self::CAMPAIGN_CONTENT : '<p>Contenido original.</p>']);
        }
        BlogPost::factory()->create(['slug' => self::PLANNING, 'path' => '/post/'.self::PLANNING]);
    }
}
