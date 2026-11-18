<?php

namespace App\Filament\Pages;

use BackedEnum;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class ManageContactPage extends ManageSiteContent
{
    protected static ?string $navigationLabel = 'Página de contacto';

    protected static ?string $title = 'Administrar página de contacto';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedEnvelope;

    protected static string|UnitEnum|null $navigationGroup = 'Páginas del sitio';

    protected static ?int $navigationSort = 9;

    protected static array $contentKeys = ['contact'];
}
