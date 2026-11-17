<?php

namespace Tests;

use DOMDocument;
use DOMXPath;

trait InspectsStructuredData
{
    protected function document(string $html): DOMXPath
    {
        $document = new DOMDocument;
        $previous = libxml_use_internal_errors(true);
        $document->loadHTML('<?xml encoding="UTF-8">'.$html, LIBXML_NONET);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        return new DOMXPath($document);
    }

    /** @return array<int, array<string, mixed>> */
    protected function schemaGraph(DOMXPath $xpath): array
    {
        $scripts = $xpath->query('//script[@type="application/ld+json"]');
        $this->assertSame(1, $scripts->length, 'The page must emit a single JSON-LD block.');
        $schema = json_decode($scripts->item(0)->textContent, true, flags: JSON_THROW_ON_ERROR);
        $this->assertSame('https://schema.org', $schema['@context']);

        return $schema['@graph'];
    }

    /**
     * Asserts one FAQPage whose questions and answers are exactly the visible ones.
     *
     * @return array<int, array{question: string, answer: string}>
     */
    protected function assertVisibleFaqMatchesSchema(DOMXPath $xpath, int $expectedQuestions): array
    {
        $graph = $this->schemaGraph($xpath);
        $faqPages = array_values(array_filter($graph, fn (array $node): bool => $node['@type'] === 'FAQPage'));
        $this->assertCount(1, $faqPages, 'Expected exactly one FAQPage.');
        $this->assertCount($expectedQuestions, $faqPages[0]['mainEntity']);

        $block = $xpath->query('//section[@class="blog-faqs"]');
        $this->assertSame(1, $block->length);
        $this->assertSame('Preguntas relacionadas', trim($xpath->evaluate('string(.//h2)', $block->item(0))));
        $questions = $xpath->query('.//h3', $block->item(0));
        $answers = $xpath->query('.//h3/following-sibling::p[1]', $block->item(0));
        $this->assertSame($expectedQuestions, $questions->length);

        $visible = [];
        foreach ($faqPages[0]['mainEntity'] as $index => $entity) {
            $this->assertSame('Question', $entity['@type']);
            $this->assertSame('Answer', $entity['acceptedAnswer']['@type']);
            $this->assertSame($questions->item($index)->textContent, $entity['name']);
            $this->assertSame($answers->item($index)->textContent, $entity['acceptedAnswer']['text']);
            $visible[] = ['question' => $entity['name'], 'answer' => $entity['acceptedAnswer']['text']];
        }

        return $visible;
    }
}
