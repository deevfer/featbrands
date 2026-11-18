<?php

namespace App\Filament\Pages;

use BackedEnum;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class ManageAbout extends ManageSiteContent
{
    protected static ?string $navigationLabel = 'Nosotros';

    protected static ?string $title = 'Administrar Nosotros';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserGroup;

    protected static string|UnitEnum|null $navigationGroup = 'Páginas del sitio';

    protected static ?int $navigationSort = 8;

    protected static array $contentKeys = ['about'];
}
