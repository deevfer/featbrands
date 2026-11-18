<?php

namespace App\Filament\Resources\BlogPosts\Pages;

use App\Filament\Concerns\HasLivePreview;
use App\Filament\Resources\BlogPosts\BlogPostResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditBlogPost extends EditRecord
{
    use HasLivePreview;

    protected static string $resource = BlogPostResource::class;

    protected string $view = 'filament.resources.blog-posts.edit-blog-post';

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    /**
     * Saves the draft without notifications and shows it even if the post is not published yet.
     */
    protected function applyPreviewDraft(): void
    {
        $this->save(shouldRedirect: false, shouldSendSavedNotification: false);

        $post = $this->getRecord();
        if (! $post->is_published || ! $post->published_at || $post->published_at->isFuture()) {
            $post->forceFill(['is_published' => true, 'published_at' => $post->published_at?->isPast() ? $post->published_at : now()])->saveQuietly();
        }
    }

    /**
     * The draft was rolled back: reload the record so the real save compares against the database.
     */
    protected function afterPreview(): void
    {
        $this->getRecord()->refresh();
    }

    protected function previewPath(?string $statePath = null): string
    {
        return implode('/', array_map(rawurlencode(...), explode('/', $this->getRecord()->path)));
    }

    protected function previewIsLocalized(): bool
    {
        return false;
    }
}
