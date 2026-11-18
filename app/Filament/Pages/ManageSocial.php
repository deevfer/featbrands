<?php

namespace App\Filament\Pages;

use BackedEnum;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class ManageSocial extends ManageSiteContent
{
    protected static ?string $navigationLabel = 'Social Media';

    protected static ?string $title = 'Administrar Social Media';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChatBubbleLeftRight;

    protected static string|UnitEnum|null $navigationGroup = 'Páginas del sitio';

    protected static ?int $navigationSort = 6;

    protected static array $contentKeys = ['social'];
}
