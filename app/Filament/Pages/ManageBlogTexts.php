<?php

namespace App\Filament\Pages;

use BackedEnum;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class ManageBlogTexts extends ManageSiteContent
{
    protected static ?string $navigationLabel = 'Textos del blog';

    protected static ?string $title = 'Administrar textos del blog';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedLanguage;

    protected static string|UnitEnum|null $navigationGroup = 'Blog';

    protected static ?int $navigationSort = 3;

    protected static array $contentKeys = ['blog'];
}
