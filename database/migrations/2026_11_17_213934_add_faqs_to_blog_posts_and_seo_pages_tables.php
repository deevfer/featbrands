<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Visible "Preguntas relacionadas" blocks; the same data feeds the FAQPage schema.
     */
    public function up(): void
    {
        Schema::table('blog_posts', function (Blueprint $table) {
            $table->json('faqs')->nullable()->after('seo_description');
        });
        Schema::table('seo_pages', function (Blueprint $table) {
            $table->json('faqs')->nullable()->after('image');
        });
    }

    public function down(): void
    {
        Schema::table('seo_pages', function (Blueprint $table) {
            $table->dropColumn('faqs');
        });
        Schema::table('blog_posts', function (Blueprint $table) {
            $table->dropColumn('faqs');
        });
    }
};
