<?php

namespace App\Filament\Pages;

use BackedEnum;
use Filament\Forms\Components\FileUpload;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class ManageNavigation extends ManageSiteContent
{
    protected static ?string $navigationLabel = 'Menú, footer y logos';

    protected static ?string $title = 'Administrar menú, footer y logos';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBars3;

    protected static string|UnitEnum|null $navigationGroup = 'Páginas del sitio';

    protected static ?int $navigationSort = 11;

    protected static array $contentKeys = ['global'];

    protected function mediaSchema(): array
    {
        return [
            $this->logo('logo_light', 'Logo claro (menú, footer y Makitha)'),
            $this->logo('logo_dark', 'Logo oscuro (Nosotros y Social Media)'),
            $this->logo('favicon', 'Ícono de la pestaña del navegador'),
        ];
    }

    private function logo(string $name, string $label): FileUpload
    {
        return FileUpload::make($name)
            ->label($label)
            ->helperText('Vacío = logo original de Feat.')
            ->image()
            ->acceptedFileTypes(['image/svg+xml', 'image/png', 'image/webp'])
            ->maxSize(2048)
            ->disk('public')
            ->directory('site/logos')
            ->visibility('public');
    }
}
