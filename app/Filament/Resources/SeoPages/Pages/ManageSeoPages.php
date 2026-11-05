<?php

namespace App\Filament\Resources\SeoPages\Pages;

use App\Filament\Resources\SeoPages\SeoPageResource;
use App\Services\SiteSeo;
use Filament\Resources\Pages\ManageRecords;

class ManageSeoPages extends ManageRecords
{
    protected static string $resource = SeoPageResource::class;

    public function mount(): void
    {
        app(SiteSeo::class)->initialize();
        parent::mount();
    }
}
