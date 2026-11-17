<?php

namespace App\Http\Controllers;

use App\Services\GeoContent;
use App\Services\SiteSeo;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use XMLWriter;

class SeoController extends Controller
{
    public function robots(SiteSeo $seo): Response
    {
        if (! $seo->publicIndexingEnabled()) {
            return response("User-agent: *\nDisallow: /\n", 200, ['Content-Type' => 'text/plain; charset=UTF-8']);
        }
        $rules = '';
        foreach (['*', 'Googlebot', 'OAI-SearchBot', 'PerplexityBot'] as $agent) {
            $rules .= "User-agent: {$agent}\nAllow: /\nDisallow: /admin\nDisallow: /livewire\nDisallow: /up\n\n";
        }

        return response($rules.'Sitemap: '.$seo->absolute('/sitemap.xml')."\n", 200, ['Content-Type' => 'text/plain; charset=UTF-8']);
    }

    public function llms(SiteSeo $seo, GeoContent $geo): Response
    {
        abort_unless((bool) $seo->settings()['llms_enabled'], 404);

        return response($geo->llms($seo), 200, ['Content-Type' => 'text/plain; charset=UTF-8']);
    }

    public function sitemap(Request $request, SiteSeo $seo): Response
    {
        $writer = new XMLWriter;
        $writer->openMemory();
        $writer->startDocument('1.0', 'UTF-8');
        $section = $request->route('section');
        $locale = $request->route('locale');
        if (! $section) {
            $writer->startElement('sitemapindex');
            $writer->writeAttribute('xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9');
            foreach (['/site-pages-sitemap.xml', '/blog-posts-sitemap.xml', '/en_blog-posts-sitemap.xml', '/blog-categories-sitemap.xml', '/en_blog-categories-sitemap.xml'] as $path) {
                $writer->startElement('sitemap');
                $writer->writeElement('loc', $seo->absolute($path));
                $writer->endElement();
            }
        } else {
            $writer->startElement('urlset');
            $writer->writeAttribute('xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9');
            foreach ($seo->sitemapEntries($section, $locale) as $entry) {
                $writer->startElement('url');
                $writer->writeElement('loc', $seo->absolute($entry['path']));
                if ($entry['lastmod']) {
                    $writer->writeElement('lastmod', $entry['lastmod']);
                }
                $writer->endElement();
            }
        }
        $writer->endElement();
        $writer->endDocument();

        return response($writer->outputMemory(), 200, ['Content-Type' => 'application/xml; charset=UTF-8']);
    }
}
