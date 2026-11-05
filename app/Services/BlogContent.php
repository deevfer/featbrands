<?php

namespace App\Services;

use DOMDocument;
use DOMXPath;
use Illuminate\Support\Str;

class BlogContent
{
    /** @return array{html: string, headings: array<int, array{id: string, text: string}>} */
    public function prepare(string $content): array
    {
        $safe = Str::sanitizeHtml($content);
        $document = new DOMDocument;
        $previous = libxml_use_internal_errors(true);
        $document->loadHTML('<?xml encoding="UTF-8"><div id="blog-document">'.$safe.'</div>', LIBXML_NONET);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);
        $xpath = new DOMXPath($document);
        $headings = [];
        foreach ($xpath->query('//img[@src]') as $image) {
            $image->setAttribute('decoding', 'async');
            $src = $image->getAttribute('src');
            if (str_starts_with($src, '/uploads/blog/') && ! str_contains($src, '..')) {
                $file = public_path(ltrim($src, '/'));
                $size = is_file($file) ? getimagesize($file) : false;
                if ($size) {
                    $image->setAttribute('width', (string) $size[0]);
                    $image->setAttribute('height', (string) $size[1]);
                }
            }
        }
        foreach ($xpath->query('//h2|//h3|//h4|//h5|//h6') as $heading) {
            $id = 'section-'.(count($headings) + 1);
            $heading->setAttribute('id', $id);
            $headings[] = ['id' => $id, 'text' => trim($heading->textContent)];
        }
        $root = $xpath->query('//*[@id="blog-document"]')->item(0);
        $html = '';
        foreach ($root->childNodes as $child) {
            $html .= $document->saveHTML($child);
        }

        return ['html' => $html, 'headings' => $headings];
    }
}
