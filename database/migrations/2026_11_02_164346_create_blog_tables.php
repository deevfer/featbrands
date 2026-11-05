<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('blog_categories', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();
        });
        Schema::create('blog_posts', function (Blueprint $table) {
            $table->id();
            $table->string('source_id')->nullable();
            $table->string('path', 255)->unique();
            $table->string('slug', 240);
            $table->string('locale', 2)->default('es');
            $table->string('title', 1000);
            $table->text('excerpt')->nullable();
            $table->longText('content');
            $table->string('author')->default('Feat.');
            $table->timestamp('published_at')->nullable();
            $table->timestamp('original_updated_at')->nullable();
            $table->unsignedSmallInteger('reading_minutes')->default(1);
            $table->string('cover_image', 1000)->nullable();
            $table->text('cover_alt')->nullable();
            $table->string('seo_title', 1000)->nullable();
            $table->text('seo_description')->nullable();
            $table->json('tags')->nullable();
            $table->json('translations')->nullable();
            $table->string('source_url', 1500)->nullable();
            $table->string('source_text_sha256', 64)->nullable();
            $table->string('content_text_sha256', 64)->nullable();
            $table->boolean('is_published')->default(false);
            $table->timestamps();
            $table->index(['locale', 'is_published', 'published_at']);
        });
        Schema::create('blog_category_post', function (Blueprint $table) {
            $table->foreignId('blog_category_id')->constrained()->cascadeOnDelete();
            $table->foreignId('blog_post_id')->constrained()->cascadeOnDelete();
            $table->primary(['blog_category_id', 'blog_post_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blog_category_post');
        Schema::dropIfExists('blog_posts');
        Schema::dropIfExists('blog_categories');
    }
};
