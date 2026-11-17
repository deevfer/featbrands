<?php

use App\Services\GeoContent;
use App\Services\SiteSeo;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Loads the "Preguntas relacionadas" of the three new pages into their editable SEO record.
     */
    public function up(): void
    {
        app(GeoContent::class)->seedPageFaqs(app(SiteSeo::class));
    }

    /**
     * Content only: rolling back keeps the questions, which can be edited in Filament.
     */
    public function down(): void {}
};
