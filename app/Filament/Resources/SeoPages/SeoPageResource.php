<?php

namespace App\Filament\Resources\SeoPages;

use App\Filament\Components\SearchSnippet;
use App\Filament\Resources\SeoPages\Pages\ManageSeoPages;
use App\Models\SeoPage;
use App\Services\SiteSeo;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use UnitEnum;

class SeoPageResource extends Resource
{
    protected static ?string $model = SeoPage::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMagnifyingGlass;

    protected static string|UnitEnum|null $navigationGroup = 'Visibilidad';

    protected static ?int $navigationSort = 1;

    protected static ?string $modelLabel = 'SEO de página';

    protected static ?string $pluralModelLabel = 'SEO de páginas';

    protected static ?string $recordTitleAttribute = 'title';

    public static function canCreate(): bool
    {
        return false;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('route_name')->label('Página')->disabled(),
            TextInput::make('locale')->label('Idioma')->disabled(),
            TextInput::make('title')->label('Título SEO')->live(debounce: 400)->required()->maxLength(180)->helperText('Sugerencia editorial: 15–70 caracteres, con el tema principal y la marca.')->columnSpanFull(),
            Textarea::make('description')->label('Descripción SEO')->live(debounce: 400)->required()->maxLength(320)->rows(3)->helperText('Resume lo que ofrece esta página. Se muestran hasta 170 caracteres en el HTML.')->columnSpanFull(),
            SearchSnippet::make(['title'], ['description'], fn (?SeoPage $record): string => app(SiteSeo::class)->absolute($record ? app(SiteSeo::class)->mainPath($record->route_name, $record->locale) : '/')),
            TextInput::make('image')->label('Imagen para compartir')->maxLength(1000)->regex('~^(?:/(?!/)[^?#]*|https://[^\s]+)$~u')->helperText('Opcional. Ruta pública o URL HTTPS; vacía utiliza la imagen general.')->columnSpanFull(),
            Repeater::make('faqs')->label('Preguntas relacionadas')->schema([
                TextInput::make('question')->label('Pregunta')->required()->maxLength(500),
                Textarea::make('answer')->label('Respuesta')->required()->rows(3)->maxLength(2000),
            ])->defaultItems(0)->reorderable()->collapsible()->itemLabel(fn (array $state): ?string => $state['question'] ?? null)->helperText('Se muestran al final con el título «Preguntas relacionadas» y generan el schema FAQPage con el mismo texto.')->visible(fn (?SeoPage $record): bool => in_array($record?->route_name, SiteSeo::GEO_PAGES, true))->columnSpanFull(),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table->defaultSort('route_name')->columns([
            TextColumn::make('route_name')->label('Página')->searchable(),
            TextColumn::make('locale')->label('Idioma')->badge(),
            TextColumn::make('title')->label('Título SEO')->searchable()->wrap(),
            TextColumn::make('description')->label('Descripción')->limit(75)->wrap(),
            TextColumn::make('updated_at')->label('Actualización')->since(),
        ])->filters([SelectFilter::make('locale')->label('Idioma')->options(['es' => 'Español', 'en' => 'Inglés'])])->recordActions([
            EditAction::make(),
            Action::make('open')->label('Ver página')->url(fn (SeoPage $record): string => url(app(SiteSeo::class)->mainPath($record->route_name, $record->locale)))->openUrlInNewTab(),
        ]);
    }

    public static function getPages(): array
    {
        return ['index' => ManageSeoPages::route('/')];
    }
}
