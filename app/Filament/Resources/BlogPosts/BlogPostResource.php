<?php

namespace App\Filament\Resources\BlogPosts;

use App\Filament\Resources\BlogPosts\Pages\CreateBlogPost;
use App\Filament\Resources\BlogPosts\Pages\EditBlogPost;
use App\Filament\Resources\BlogPosts\Pages\ListBlogPosts;
use App\Models\BlogPost;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Validation\Rule;
use UnitEnum;

class BlogPostResource extends Resource
{
    protected static ?string $model = BlogPost::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    protected static string|UnitEnum|null $navigationGroup = 'Blog';

    protected static ?string $modelLabel = 'artículo';

    protected static ?string $pluralModelLabel = 'Artículos';

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('title')->label('Título')->required()->maxLength(1000)->columnSpanFull(),
            Select::make('locale')->label('Idioma del artículo')->options(['es' => 'Español', 'en' => 'Inglés'])->default('es')->required()->disabledOn('edit')->dehydrated(),
            TextInput::make('slug')->label('Slug de la URL')->required()->maxLength(240)->regex('~^[^/?#]+$~u')->disabledOn('edit')->dehydrated()
                ->rules(fn (Get $get, ?BlogPost $record): array => [Rule::unique('blog_posts', 'slug')->where('locale', $get('locale'))->ignore($record?->id)])
                ->helperText('La URL original se conserva al editar el artículo.'),
            TextInput::make('author')->label('Autor')->required()->maxLength(255),
            DateTimePicker::make('published_at')->label('Fecha de publicación')->required()->seconds(false),
            TextInput::make('reading_minutes')->label('Minutos de lectura')->numeric()->minValue(1)->maxValue(300)->required()->default(1),
            Toggle::make('is_published')->label('Publicado')->default(false),
            Select::make('categories')->label('Categorías')->relationship('categories', 'name')->multiple()->preload()->searchable()->columnSpanFull(),
            Textarea::make('excerpt')->label('Extracto')->rows(3)->columnSpanFull(),
            TextInput::make('cover_image')->label('Imagen de portada')->maxLength(1000)->helperText('URL de la imagen o ruta actual.')->columnSpanFull(),
            TextInput::make('cover_alt')->label('Descripción de la imagen')->maxLength(1000)->columnSpanFull(),
            RichEditor::make('content')->label('Contenido completo')->required()->fileAttachmentsDisk('public')->fileAttachmentsDirectory('blog')->columnSpanFull(),
            TextInput::make('seo_title')->label('Título SEO')->maxLength(1000)->columnSpanFull(),
            Textarea::make('seo_description')->label('Descripción SEO')->rows(3)->columnSpanFull(),
            Repeater::make('faqs')->label('Preguntas relacionadas')->schema([
                TextInput::make('question')->label('Pregunta')->required()->maxLength(500),
                Textarea::make('answer')->label('Respuesta')->required()->rows(3)->maxLength(2000),
            ])->defaultItems(0)->reorderable()->collapsible()->itemLabel(fn (array $state): ?string => $state['question'] ?? null)->helperText('Se muestran al final con el título «Preguntas relacionadas» y generan el schema FAQPage con el mismo texto.')->columnSpanFull(),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table->defaultSort('published_at', 'desc')->columns([
            TextColumn::make('title')->label('Título')->searchable()->limit(75)->wrap(),
            TextColumn::make('locale')->label('Idioma')->badge(),
            TextColumn::make('categories.name')->label('Categorías')->badge()->separator(', '),
            TextColumn::make('author')->label('Autor')->searchable(),
            TextColumn::make('published_at')->label('Publicado el')->date('d/m/Y')->sortable(),
            IconColumn::make('is_published')->label('Publicado')->boolean(),
        ])->filters([
            SelectFilter::make('locale')->label('Idioma')->options(['es' => 'Español', 'en' => 'Inglés']),
            SelectFilter::make('categories')->label('Categoría')->relationship('categories', 'name'),
            TernaryFilter::make('is_published')->label('Publicado'),
        ])->recordActions([
            EditAction::make(),
            Action::make('open')->label('Ver artículo')->url(fn (BlogPost $record): string => url($record->path))->openUrlInNewTab()->visible(fn (BlogPost $record): bool => $record->is_published),
        ]);
    }

    public static function getPages(): array
    {
        return ['index' => ListBlogPosts::route('/'), 'create' => CreateBlogPost::route('/create'), 'edit' => EditBlogPost::route('/{record}/edit')];
    }
}
