<?php

namespace App\Services;

use App\Models\SeoPage;
use Illuminate\Support\Facades\File;

/**
 * Copy from "Contenido para implementar — featbrands.docx" (resources/data/geo.json), kept verbatim.
 */
class GeoContent
{
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
        $copy = $this->data()['pages'][$page];
        $blocks = array_map(fn (array $block): array => $block['type'] === 'ul'
            ? ['type' => 'ul', 'items' => $block['items']]
            : ['type' => $block['type'], 'html' => $this->linked($block['text'], $block['links'] ?? [])], $copy['body']);

        return ['name' => $copy['h1'], 'intro' => $copy['body'][0]['text'], 'h1' => $copy['h1'], 'blocks' => $blocks];
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
