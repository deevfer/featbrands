<?php

use App\Models\BlogPost;
use App\Services\GeoContent;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Adds the document's FAQs to existing posts and the new block to the campaign post.
     * Fails if any target post is missing. On an empty database (before blog:import) it does
     * nothing: app:setup applies the same content right after importing the blog.
     */
    public function up(): void
    {
        if (! BlogPost::query()->exists()) {
            return;
        }

        app(GeoContent::class)->applyToPosts();
    }

    /**
     * Content only: posts are not reverted automatically.
     */
    public function down(): void {}
};
