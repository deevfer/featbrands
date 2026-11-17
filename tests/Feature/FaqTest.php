<?php

namespace Tests\Feature;

use App\Models\BlogPost;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\InspectsStructuredData;
use Tests\TestCase;

class FaqTest extends TestCase
{
    use InspectsStructuredData, LazilyRefreshDatabase;

    public function test_post_faqs_render_a_visible_block_and_an_identical_faq_page_schema(): void
    {
        $faqs = [
            ['question' => '¿Qué incluye el servicio?', 'answer' => 'Estrategia, creatividad y medios bajo un mismo equipo, como en "Feat."'],
            ['question' => '¿Dónde se contrata?', 'answer' => 'En Ciudad de Guatemala & Costa Rica.'],
        ];
        BlogPost::factory()->create(['slug' => 'post-con-preguntas', 'path' => '/post/post-con-preguntas', 'faqs' => $faqs]);

        $xpath = $this->document($this->get('/post/post-con-preguntas')->assertOk()->getContent());

        $this->assertSame($faqs, $this->assertVisibleFaqMatchesSchema($xpath, 2));
        $this->assertSame(1, $xpath->query('//h1')->length);
    }

    public function test_posts_without_faqs_have_no_block_or_faq_page_and_empty_entries_are_ignored(): void
    {
        BlogPost::factory()->create(['slug' => 'sin-preguntas', 'path' => '/post/sin-preguntas', 'faqs' => [['question' => ' ', 'answer' => 'Sin pregunta']]]);

        $xpath = $this->document($this->get('/post/sin-preguntas')->assertOk()->getContent());

        $this->assertSame(0, $xpath->query('//section[@class="blog-faqs"]')->length);
        $this->assertNotContains('FAQPage', array_column($this->schemaGraph($xpath), '@type'));
    }

    public function test_changing_post_faqs_updates_its_modification_date(): void
    {
        $this->freezeTime();
        $post = BlogPost::factory()->create(['original_updated_at' => now()->subYear()]);

        $post->update(['faqs' => [['question' => '¿Pregunta?', 'answer' => 'Respuesta.']]]);

        $this->assertSame(now()->toDateTimeString(), $post->fresh()->original_updated_at->toDateTimeString());
    }
}
