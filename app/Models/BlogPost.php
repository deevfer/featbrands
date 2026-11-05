<?php

namespace App\Models;

use Database\Factories\BlogPostFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[Fillable(['source_id', 'path', 'slug', 'locale', 'title', 'excerpt', 'content', 'author', 'published_at', 'original_updated_at', 'reading_minutes', 'cover_image', 'cover_alt', 'seo_title', 'seo_description', 'tags', 'translations', 'source_url', 'source_text_sha256', 'content_text_sha256', 'is_published'])]
class BlogPost extends Model
{
    /** @use HasFactory<BlogPostFactory> */
    use HasFactory;

    protected static function booted(): void
    {
        static::saving(function (BlogPost $post): void {
            $post->path = ($post->locale === 'en' ? '/en' : '').'/post/'.$post->slug;
            if ($post->exists && $post->isDirty(['title', 'content', 'excerpt', 'author', 'seo_title', 'seo_description', 'cover_image']) && ! $post->isDirty('original_updated_at')) {
                $post->original_updated_at = now();
            }
        });
    }

    protected function casts(): array
    {
        return ['published_at' => 'datetime', 'original_updated_at' => 'datetime', 'tags' => 'array', 'translations' => 'array', 'is_published' => 'boolean'];
    }

    public function coverImageUrl(): ?string
    {
        if (! $this->cover_image) {
            return null;
        }

        if (preg_match('~^https?://~i', $this->cover_image)) {
            return $this->cover_image;
        }

        return asset(str_starts_with($this->cover_image, '/') ? ltrim($this->cover_image, '/') : 'storage/'.$this->cover_image);
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(BlogCategory::class, 'blog_category_post');
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true)->whereNotNull('published_at')->where('published_at', '<=', now());
    }
}
