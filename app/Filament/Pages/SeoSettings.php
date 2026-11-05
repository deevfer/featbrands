<?php

namespace App\Filament\Pages;

use App\Models\SeoSetting;
use App\Services\SiteSeo;
use BackedEnum;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Arr;
use UnitEnum;

class SeoSettings extends Page
{
    protected static ?string $title = 'Configuración SEO';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected static string|UnitEnum|null $navigationGroup = 'Visibilidad';

    protected static ?int $navigationSort = 2;

    protected string $view = 'filament.pages.seo-settings';

    /** @var array<string, mixed>|null */
    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill(app(SiteSeo::class)->settings());
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Dominio e indexación')->description('Estas opciones se aplican al SEO público. Los entornos locales conservan noindex hasta usar APP_ENV=production.')->schema([
                TextInput::make('public_url')->label('Dominio público canónico')->required()->url()->maxLength(255)->regex('~^https://(?:[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?\.)+[a-z]{2,63}/?$~i')->helperText('Solo el origen HTTPS, sin subcarpetas. En HostGator configura también APP_URL con este mismo dominio.'),
                Toggle::make('indexing_enabled')->label('Permitir indexación en producción')->default(true),
                TextInput::make('default_image')->label('Imagen para compartir por defecto')->required()->maxLength(1000)->regex('~^(?:/(?!/)[^?#]*|https://[^\s]+)$~u')->helperText('Ruta pública o URL HTTPS. Ejemplo: /assets/feat-social.png'),
            ]),
            Section::make('Verificación de herramientas')->description('Pega únicamente el valor content del código que entrega cada herramienta. Guardarlo no confirma que la cuenta esté verificada.')->schema([
                TextInput::make('search_console_verification')->label('Google Search Console')->maxLength(255)->regex('/^[A-Za-z0-9_-]*$/'),
                TextInput::make('bing_verification')->label('Bing Webmaster Tools')->maxLength(255)->regex('/^[A-Za-z0-9_-]*$/'),
            ]),
        ])->statePath('data');
    }

    public function save(): void
    {
        $state = Arr::only($this->form->getState(), ['public_url', 'indexing_enabled', 'default_image', 'search_console_verification', 'bing_verification']);
        $settings = SeoSetting::firstOrNew(['id' => 1]);
        $previous = $settings->data ?? [];
        $state['public_url'] = rtrim($state['public_url'], '/');
        if (($previous['public_url'] ?? config('seo.public_url')) !== $state['public_url'] || ($previous['indexing_enabled'] ?? config('seo.indexing_enabled')) !== $state['indexing_enabled']) {
            unset($previous['public_verification']);
        }
        $settings->data = array_replace($previous, $state);
        $settings->save();
        Notification::make()->title('Configuración guardada. Vuelve a analizar para actualizar la nota.')->success()->send();
    }
}
