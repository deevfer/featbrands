<?php

namespace App\Services;

use App\Models\BlogPost;
use App\Models\SeoPage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use RuntimeException;

/**
 * Copy from "Contenido para implementar — featbrands.docx" (resources/data/geo.json), kept verbatim.
 */
class GeoContent
{
    public function __construct(private SiteContent $content) {}

    /** @return array<string, mixed> */
    public function data(): array
    {
        return File::json(resource_path('data/geo.json'));
    }

    /**
     * @return array{name: string, intro: string, h1: string, blocks: array<int, array{type: string, html?: string, items?: array<int, string>}>}
     */
    public function page(string $page): array
    {
        $copy = $this->content->get($page, 'es');
        $blocks = array_map(fn (array $block): array => $block['type'] === 'ul'
            ? ['type' => 'ul', 'items' => $block['items']]
            : ['type' => $block['type'], 'html' => $this->linked($block['text'], $block['links'] ?? [])], $copy['body']);

        return ['name' => $copy['h1'], 'intro' => $copy['body'][0]['text'], 'h1' => $copy['h1'], 'blocks' => $blocks];
    }

    /**
     * llms.txt: title, summary and "Páginas clave" with absolute URLs, skipping unpublished posts.
     */
    public function llms(SiteSeo $seo): string
    {
        $copy = $this->data()['llms'];
        $lines = ['# '.$copy['title'], '', '> '.$copy['summary'], '', '## '.$copy['section']];
        foreach ($copy['links'] as $link) {
            if (str_starts_with($link['path'], '/post/') && ! BlogPost::published()->where('path', $link['path'])->exists()) {
                continue;
            }
            $lines[] = '- ['.$link['title'].']('.$seo->absolute($link['path']).')';
        }

        return implode("\n", $lines)."\n";
    }

    /**
     * Seeds the editable FAQs of the new pages without overwriting edits made in Filament.
     */
    public function seedPageFaqs(SiteSeo $seo): void
    {
        foreach ($this->data()['pages'] as $page => $copy) {
            $record = SeoPage::firstOrCreate(['route_name' => $page, 'locale' => 'es'], $seo->defaults()['es'][$page]);
            if ($seo->normalizeFaqs($record->faqs) === []) {
                $record->update(['faqs' => $copy['faqs']]);
            }
        }
    }

    /**
     * Adds the document's FAQs (and the campaign block) to existing posts, found by slug. Idempotent.
     *
     * @throws RuntimeException when one of the posts does not exist.
     */
    public function applyToPosts(): void
    {
        DB::transaction(function (): void {
            foreach ($this->data()['posts'] as $slug => $changes) {
                $post = BlogPost::where('locale', 'es')->where('slug', $slug)->first()
                    ?? throw new RuntimeException("Contenido GEO: no existe el post con slug «{$slug}». Importa el blog antes de aplicar este contenido.");
                $attributes = ['faqs' => $changes['faqs']];
                if (isset($changes['insert_after_intro'])) {
                    $attributes['content'] = $this->withBlockAfterIntro($post->content, $changes['insert_after_intro']);
                }
                $post->update($attributes);
            }
        });
    }

    /**
     * Inserts the block right after the first paragraph, leaving the rest of the HTML untouched.
     *
     * @param  array{intro: string, steps: array<int, string>, link: array{text: string, slug: string}}  $block
     */
    private function withBlockAfterIntro(string $content, array $block): string
    {
        $intro = '<p>'.e($block['intro']).'</p>';
        $introEnd = strpos($content, '</p>');
        if (str_contains($content, $intro) || $introEnd === false) {
            return $content;
        }

        $html = $intro;
        foreach ($block['steps'] as $step) {
            [$lead, $rest] = explode('. ', $step, 2);
            $html .= '<p><strong>'.e($lead).'.</strong> '.e($rest).'</p>';
        }
        $target = BlogPost::published()->where('locale', 'es')->where('slug', $block['link']['slug'])->value('path');
        $html .= '<p>'.($target
            ? '<a href="'.e(implode('/', array_map(rawurlencode(...), explode('/', $target)))).'">'.e($block['link']['text']).'</a>'
            : e($block['link']['text'])).'</p>';

        return substr($content, 0, $introEnd + 4).$html.substr($content, $introEnd + 4);
    }

    /**
     * Escapes the text and turns the first occurrence of each phrase into a link.
     *
     * @param  array<int, array{text: string, href: string}>  $links
     */
    private function linked(string $text, array $links): string
    {
        $html = e($text);
        foreach ($links as $link) {
            $phrase = e($link['text']);
            $position = mb_strpos($html, $phrase);
            if ($position !== false) {
                $html = mb_substr($html, 0, $position).'<a href="'.e($link['href']).'">'.$phrase.'</a>'.mb_substr($html, $position + mb_strlen($phrase));
            }
        }

        return $html;
    }
}
