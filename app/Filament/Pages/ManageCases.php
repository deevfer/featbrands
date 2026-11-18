<?php

namespace App\Filament\Pages;

use BackedEnum;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class ManageCases extends ManageSiteContent
{
    protected static ?string $navigationLabel = 'Casos';

    protected static ?string $title = 'Administrar Casos';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static string|UnitEnum|null $navigationGroup = 'Páginas del sitio';

    protected static ?int $navigationSort = 7;

    protected static array $contentKeys = ['cases'];
}
