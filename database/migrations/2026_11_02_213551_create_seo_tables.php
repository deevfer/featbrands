<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('seo_pages', function (Blueprint $table) {
            $table->id();
            $table->string('route_name');
            $table->string('locale', 2);
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->string('image', 1000)->nullable();
            $table->timestamps();
            $table->unique(['route_name', 'locale']);
        });
        Schema::create('seo_settings', function (Blueprint $table) {
            $table->id();
            $table->json('data');
            $table->timestamps();
        });
        Schema::create('seo_audits', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('score');
            $table->unsignedInteger('page_count');
            $table->json('report');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('seo_audits');
        Schema::dropIfExists('seo_settings');
        Schema::dropIfExists('seo_pages');
    }
};
