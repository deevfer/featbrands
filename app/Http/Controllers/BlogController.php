<?php

namespace App\Http\Controllers;

use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Services\BlogContent;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class BlogController extends Controller
{
    public function index(Request $request, SiteController $site): View
    {
        $term = $request->query('q');
        abort_if(is_array($term) || (is_string($term) && mb_strlen($term) > 200), 404);

        return $this->listing($request, $site, term: is_string($term) ? trim($term) : null);
    }

    public function category(Request $request, SiteController $site, string $slug): View
    {
        $category = BlogCategory::where('slug', $slug)->firstOrFail();

        return $this->listing($request, $site, $category);
    }

    public function search(Request $request, SiteController $site, string $term): View
    {
        abort_if(mb_strlen($term) > 200, 404);

        return $this->listing($request, $site, term: $term);
    }

    public function tag(Request $request, SiteController $site, string $slug): View
    {
        return $this->listing($request, $site, tag: $slug);
    }

    public function post(Request $request, SiteController $site, BlogContent $content, string $slug): View
    {
        $path = ($request->is('en/*') ? '/en' : '').'/post/'.$slug;
        $post = BlogPost::published()->with('categories')->where('path', $path)->firstOrFail();
        $locale = $this->locale($request);
        $request->attributes->set('site_locale', $locale);
        $related = BlogPost::published()->where('locale', $post->locale)->whereKeyNot($post->id);
        if ($post->categories->isNotEmpty()) {
            $related->whereHas('categories', fn (Builder $query): Builder => $query->whereIn('blog_categories.id', $post->categories->modelKeys()));
        }
        $translations = $post->translations ?? [];
        $languageUrls = [];
        foreach (['es', 'en'] as $language) {
            $target = isset($translations[$language]) && BlogPost::published()->where('path', $translations[$language])->exists()
                ? $translations[$language] : $post->path;
            $query = ['market' => $request->query('market', 'gt')];
            if (($language === 'en' && ! str_starts_with($target, '/en/')) || ($language === 'es' && str_starts_with($target, '/en/'))) {
                $query['lang'] = $language;
            }
            $languageUrls[$language] = url($target).'?'.http_build_query($query);
        }
        $prepared = $content->prepare($post->content);
        $canonical = url($post->path);

        return $site->render($request, 'blog', [
            'viewName' => 'site.blog.post', 'post' => $post,
            'articleHtml' => $prepared['html'], 'articleHeadings' => $prepared['headings'],
            'related' => $related->orderByDesc('published_at')->limit(3)->get(),
            'pageTitle' => ($post->seo_title ?: $post->title).' — Feat.',
            'pageDescription' => $post->seo_description ?: $post->excerpt,
            'canonicalUrl' => $canonical, 'languageUrls' => $languageUrls,
            'articleSchema' => [
                '@context' => 'https://schema.org', '@type' => 'BlogPosting', 'headline' => $post->title,
                'description' => $post->seo_description, 'datePublished' => $post->published_at->toIso8601String(),
                'dateModified' => ($post->original_updated_at ?? $post->updated_at)->toIso8601String(),
                'author' => ['@type' => 'Person', 'name' => $post->author],
                'publisher' => ['@type' => 'Organization', 'name' => 'Feat.', 'url' => route('home')],
                'url' => $canonical, 'inLanguage' => $post->locale,
                ...($post->cover_image ? ['image' => $post->coverImageUrl()] : []),
            ],
        ]);
    }

    private function locale(Request $request): string
    {
        if (in_array($request->query('lang'), ['es', 'en'], true)) {
            return $request->query('lang');
        }

        return $request->is('en/*') ? 'en' : 'es';
    }

    private function listing(Request $request, SiteController $site, ?BlogCategory $category = null, ?string $term = null, ?string $tag = null): View
    {
        $locale = $this->locale($request);
        $request->attributes->set('site_locale', $locale);
        $query = BlogPost::published()->with('categories')->where('locale', $locale);
        if ($category) {
            $query->whereHas('categories', fn (Builder $q): Builder => $q->whereKey($category->id));
        }
        if ($term) {
            $like = '%'.str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $term).'%';
            $query->where(fn (Builder $q): Builder => $q->whereLike('title', $like)->orWhereLike('excerpt', $like)->orWhereLike('content', $like));
        }
        if ($tag) {
            $ids = (clone $query)->get(['id', 'tags'])->filter(fn (BlogPost $post): bool => collect($post->tags)->contains(fn (array $item): bool => Str::lower($item['slug']) === Str::lower($tag)))->modelKeys();
            $query->whereKey($ids);
        }
        $pageNumber = max(1, (int) ($request->route('page') ?? $request->query('page', 1)));
        $posts = $query->orderByDesc('published_at')->orderByDesc('id')->paginate(20, ['*'], 'page', $pageNumber);
        abort_if($pageNumber > $posts->lastPage(), 404);
        $base = preg_replace('~/page/\d+$~', '', '/'.$request->decodedPath());
        $languageUrls = [];
        foreach (['es', 'en'] as $language) {
            $path = ($language === 'en' ? '/en' : '').preg_replace('~^/en(?=/)~', '', $base);
            $languageUrls[$language] = url($path).'?'.http_build_query(['market' => $request->query('market', 'gt')]);
        }
        $categories = BlogCategory::orderBy('position')->withCount(['posts' => fn (Builder $q): Builder => $q->published()->where('locale', $locale)])->get();

        return $site->render($request, 'blog', [
            'viewName' => 'site.blog.index', 'posts' => $posts, 'blogCategories' => $categories,
            'selectedCategory' => $category, 'searchTerm' => $term, 'selectedTag' => $tag,
            'listBasePath' => $base, 'canonicalUrl' => url('/'.$request->decodedPath()), 'languageUrls' => $languageUrls,
            'pageTitle' => ($category?->name ?? ($term ? 'Blog: '.$term : 'Blog')).' — Feat.',
        ]);
    }
}
