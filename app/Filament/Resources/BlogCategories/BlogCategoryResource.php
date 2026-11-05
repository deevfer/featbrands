<?php

namespace App\Filament\Resources\BlogCategories;

use App\Filament\Resources\BlogCategories\Pages\ManageBlogCategories;
use App\Models\BlogCategory;
use BackedEnum;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class BlogCategoryResource extends Resource
{
    protected static ?string $model = BlogCategory::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTag;

    protected static string|UnitEnum|null $navigationGroup = 'Blog';

    protected static ?string $modelLabel = 'categoría';

    protected static ?string $pluralModelLabel = 'Categorías';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')->label('Nombre')->required()->maxLength(255),
            TextInput::make('slug')->label('Slug de la URL')->required()->maxLength(255)->regex('~^[^/?#]+$~u')->unique(ignoreRecord: true)->disabledOn('edit')->dehydrated(),
            Textarea::make('description')->label('Descripción')->rows(3)->columnSpanFull(),
            TextInput::make('position')->label('Orden')->numeric()->minValue(0)->default(0)->required(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->defaultSort('position')->columns([
            TextColumn::make('name')->label('Nombre')->searchable(),
            TextColumn::make('slug')->label('Slug'),
            TextColumn::make('posts_count')->label('Artículos')->counts('posts'),
            TextColumn::make('position')->label('Orden')->sortable(),
        ])->recordActions([EditAction::make()]);
    }

    public static function getPages(): array
    {
        return ['index' => ManageBlogCategories::route('/')];
    }
}
