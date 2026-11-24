<?php

namespace App\Filament\Pages;

use BackedEnum;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class ManageEmailTexts extends ManageSiteContent
{
    protected static ?string $navigationLabel = 'Textos de correos';

    protected static ?string $title = 'Administrar textos de correos';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedEnvelopeOpen;

    protected static string|UnitEnum|null $navigationGroup = 'Configuración';

    protected static ?int $navigationSort = 2;

    protected static array $contentKeys = ['email_confirmation', 'email_lead', 'email_abandoned'];
}
