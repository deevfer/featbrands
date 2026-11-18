<?php

namespace App\Filament\Pages;

use BackedEnum;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class ManageGeoPages extends ManageSiteContent
{
    protected static ?string $navigationLabel = 'Páginas GEO';

    protected static ?string $title = 'Administrar páginas GEO';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedGlobeAmericas;

    protected static string|UnitEnum|null $navigationGroup = 'Páginas del sitio';

    protected static ?int $navigationSort = 10;

    protected static array $contentKeys = [
        'mejor-agencia-publicidad-guatemala',
        'mejorar-comunicacion-marca-guatemala',
        'publicidad-digital-guatemala-city',
    ];
}
