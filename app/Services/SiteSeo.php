<?php

namespace App\Services;

use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\SeoPage;
use App\Models\SeoSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class SiteSeo
{
    public const PAGES = ['home', 'performance', 'brand', 'social', 'experience', 'makitha', 'cases', 'about', 'contact', 'blog'];

    /** @return array<string, mixed> */
    public function settings(): array
    {
        $saved = Schema::hasTable('seo_settings') ? SeoSetting::find(1)?->data ?? [] : [];

        return array_replace(['public_url' => config('seo.public_url'), 'indexing_enabled' => config('seo.indexing_enabled'), 'search_console_verification' => '', 'bing_verification' => '', 'default_image' => '/assets/feat-social.png'], $saved);
    }

    public function origin(): string
    {
        return rtrim((string) $this->settings()['public_url'], '/');
    }

    public function absolute(string $path): string
    {
        $parts = explode('?', $path, 2);
        $encoded = implode('/', array_map(fn (string $segment): string => rawurlencode(rawurldecode($segment)), explode('/', $parts[0])));

        return $this->origin().'/'.ltrim($encoded, '/').(isset($parts[1]) ? '?'.$parts[1] : '');
    }

    public function publicIndexingEnabled(): bool
    {
        return app()->environment('production') && (bool) $this->settings()['indexing_enabled'];
    }

    public function mainPath(string $page, string $locale): string
    {
        if ($page === 'blog') {
            return $locale === 'en' ? '/en/blog' : '/blog';
        }

        return route($page, $locale === 'en' ? ['lang' => 'en'] : [], false);
    }

    /** @return array<string, array<string, array{title: string, description: string}>> */
    public function defaults(): array
    {
        return File::json(resource_path('data/seo.json'));
    }

    public function initialize(): void
    {
        foreach ($this->defaults() as $locale => $pages) {
            foreach ($pages as $page => $data) {
                SeoPage::firstOrCreate(['route_name' => $page, 'locale' => $locale], $data);
            }
        }
    }

    /** @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    public function metadata(Request $request, array $data): array
    {
        $page = $data['page'];
        $locale = $data['locale'];
        $post = $data['post'] ?? null;
        $settings = $this->settings();
        $custom = Schema::hasTable('seo_pages') ? SeoPage::where('route_name', $page)->where('locale', $locale)->first() : null;
        $defaults = $this->defaults()[$locale][$page] ?? [];
        $title = $custom?->title ?: ($defaults['title'] ?? $data['pageTitle']);
        $description = $custom?->description ?: ($defaults['description'] ?? $data['pageDescription']);
        $image = $custom?->image ?: $settings['default_image'];
        $alternates = [];
        $indexable = true;
        $path = $post instanceof BlogPost ? $post->path : $this->mainPath($page, $locale);

        if ($post instanceof BlogPost) {
            $path = $post->path;
            $articleTitle = $post->seo_title ?: $post->title;
            if (! $post->seo_title || $post->seo_title === $post->title) {
                if (mb_strlen($articleTitle) > 60) {
                    $words = preg_split('/\s+/u', $articleTitle);
                    $tail = Str::limit(implode(' ', array_slice($words, -4)), 36, '…', preserveWords: true);
                    $articleTitle = Str::limit($articleTitle, 58 - mb_strlen($tail), '', preserveWords: true).'… '.$tail;
                }
            }
            $title = $articleTitle.' — Feat.';
            $description = $post->seo_description;
            if (mb_strlen(trim((string) $description)) < 60) {
                $description = $post->excerpt ?: $post->content;
            }
            if (mb_strlen(trim(strip_tags((string) $description))) < 60) {
                $description = $post->content;
            }
            $image = $post->cover_image ?: $settings['default_image'];
            if ($post->cover_image && ! str_starts_with($image, '/') && ! preg_match('~^https?://~i', $image)) {
                $image = '/storage/'.$image;
            }
            $indexable = $locale === $post->locale;
            $alternates[$post->locale] = $this->absolute($post->path);
            foreach ($post->translations ?? [] as $language => $translation) {
                if (BlogPost::published()->where('path', $translation)->exists()) {
                    $alternates[$language] = $this->absolute($translation);
                }
            }
        } elseif ($page === 'blog') {
            $path = ($locale === 'en' ? '/en' : '').preg_replace('~^/en(?=/)~', '', '/'.$request->decodedPath());
            if ($data['posts']->currentPage() > 1 && ! $request->route('page')) {
                $path .= '/page/'.$data['posts']->currentPage();
            }
            $term = $data['searchTerm'] ?? null;
            if ($request->route('page')) {
                $title = ($locale === 'en' ? 'Page ' : 'Página ').$request->route('page').' | '.$title;
            }
            if ($data['selectedCategory'] ?? null) {
                $title = $data['selectedCategory']->name.' | Blog — Feat.';
                $description = $data['selectedCategory']->description ?: $description;
                $indexable = $data['posts']->total() > 0;
            }
            if ($term || ($data['selectedTag'] ?? null)) {
                $indexable = false;
                $title = ($term ?: $data['selectedTag']).' | Blog — Feat.';
            }
            if ($request->query('q') && is_string($term)) {
                $path = ($locale === 'en' ? '/en' : '').'/blog/search/'.$term;
            }
            if ($path === '/blog' || $path === '/en/blog') {
                $alternates = ['es' => $this->absolute('/blog'), 'en' => $this->absolute('/en/blog')];
            }
        } else {
            foreach (['es', 'en'] as $language) {
                $alternates[$language] = $this->absolute($this->mainPath($page, $language));
            }
        }

        $description = $this->plain((string) $description, 170);
        $canonical = $this->absolute($path);
        $imageUrl = preg_match('~^https?://~i', (string) $image) ? $image : $this->absolute('/'.ltrim((string) $image, '/'));
        $organization = ['@type' => 'Organization', '@id' => $this->origin().'/#organization', 'name' => 'Feat.', 'url' => $this->origin().'/', 'logo' => $this->absolute('/assets/feat-black.svg')];
        $graph = [$organization];
        $graph[] = ['@type' => $page === 'blog' && ! $post ? 'CollectionPage' : 'WebPage', '@id' => $canonical.'#webpage', 'url' => $canonical, 'name' => $title, 'description' => $description, 'inLanguage' => $post?->locale ?? $locale, 'publisher' => ['@id' => $organization['@id']]];
        if ($page === 'home') {
            $graph[] = ['@type' => 'WebSite', '@id' => $this->origin().'/#website', 'url' => $this->origin().'/', 'name' => 'Feat.', 'publisher' => ['@id' => $organization['@id']], 'inLanguage' => ['es', 'en']];
        }
        if (in_array($page, ['performance', 'brand', 'social', 'experience', 'makitha'], true)) {
            $graph[] = ['@type' => 'Service', 'name' => $data['copy']['name'] ?? 'Performance & Connections', 'description' => $description, 'url' => $canonical, 'provider' => ['@id' => $organization['@id']]];
        }
        if ($post instanceof BlogPost) {
            $graph[] = ['@type' => 'BlogPosting', 'headline' => $post->title, 'description' => $description, 'url' => $canonical, 'mainEntityOfPage' => ['@id' => $canonical.'#webpage'], 'inLanguage' => $post->locale, 'author' => ['@type' => 'Person', 'name' => $post->author], 'publisher' => ['@id' => $organization['@id']], 'datePublished' => $post->published_at->toIso8601String(), 'dateModified' => ($post->original_updated_at ?? $post->updated_at)->toIso8601String(), 'image' => $imageUrl];
        }
        if ($page !== 'home') {
            $crumbs = [['@type' => 'ListItem', 'position' => 1, 'name' => $locale === 'en' ? 'Home' : 'Inicio', 'item' => $this->absolute($this->mainPath('home', $locale))]];
            if ($post) {
                $crumbs[] = ['@type' => 'ListItem', 'position' => 2, 'name' => 'Blog', 'item' => $this->absolute($this->mainPath('blog', $post->locale))];
            }
            $crumbs[] = ['@type' => 'ListItem', 'position' => count($crumbs) + 1, 'name' => $post?->title ?? $title, 'item' => $canonical];
            $graph[] = ['@type' => 'BreadcrumbList', 'itemListElement' => $crumbs];
        }

        return ['title' => $title, 'description' => $description, 'canonical' => $canonical, 'alternates' => $alternates, 'image' => $imageUrl, 'image_alt' => $post?->cover_alt ?: 'Feat. — Collab Driven Growth', 'locale' => $post?->locale ?? $locale, 'type' => $post ? 'article' : 'website', 'robots' => $this->publicIndexingEnabled() && $indexable ? 'index, follow, max-image-preview:large' : 'noindex, follow', 'schema' => ['@context' => 'https://schema.org', '@graph' => $graph], 'verification' => $settings, 'published' => $post?->published_at?->toIso8601String(), 'modified' => $post ? ($post->original_updated_at ?? $post->updated_at)->toIso8601String() : null];
    }

    public function plain(string $value, int $limit = 170): string
    {
        $value = preg_replace('~<(?:br\b[^>]*|/(?:p|div|h[1-6]|li|blockquote|figure|figcaption))>~i', ' ', $value);
        $text = preg_replace('/\s+/u', ' ', html_entity_decode(strip_tags($value), ENT_QUOTES | ENT_HTML5, 'UTF-8'));

        return Str::limit(trim($text), $limit, '…');
    }

    /** @return array<int, array{path: string, lastmod: ?string}> */
    public function sitemapEntries(?string $section = null, ?string $locale = null): array
    {
        $entries = [];
        if (! $section || $section === 'pages') {
            foreach (['es', 'en'] as $language) {
                if ($locale && $language !== $locale) {
                    continue;
                }
                foreach (self::PAGES as $page) {
                    $entries[] = ['path' => $this->mainPath($page, $language), 'lastmod' => null];
                }
            }
        }
        if (! $section || $section === 'posts') {
            foreach (BlogPost::published()->when($locale, fn ($q) => $q->where('locale', $locale))->orderBy('id')->cursor() as $post) {
                $entries[] = ['path' => $post->path, 'lastmod' => ($post->original_updated_at ?? $post->published_at)->toIso8601String()];
            }
        }
        if (! $section || $section === 'categories') {
            foreach (['es', 'en'] as $language) {
                if ($locale && $language !== $locale) {
                    continue;
                }
                foreach (BlogCategory::whereHas('posts', fn ($q) => $q->published()->where('locale', $language))->orderBy('id')->get() as $category) {
                    $entries[] = ['path' => ($language === 'en' ? '/en' : '').'/blog/categories/'.$category->slug, 'lastmod' => null];
                }
            }
        }

        return $entries;
    }
}
