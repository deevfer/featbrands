<?php

namespace App\Filament\Pages;

use App\Filament\Concerns\HasLivePreview;
use App\Models\BlogPost;
use App\Services\SiteIcons;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

/**
 * Every icon of the site: each one can be swapped for another symbol or an image.
 */
class ManageIcons extends Page
{
    use HasLivePreview;

    protected string $view = 'filament.pages.manage-site-content';

    protected static ?string $navigationLabel = 'Íconos';

    protected static ?string $title = 'Administrar íconos';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSparkles;

    protected static string|UnitEnum|null $navigationGroup = 'Páginas del sitio';

    protected static ?int $navigationSort = 12;

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill(['icons' => app(SiteIcons::class)->stored()]);
    }

    public function form(Schema $schema): Schema
    {
        $tabs = [];

        foreach (SiteIcons::GROUPS as $group => $definition) {
            $fields = [];
            foreach ($definition['icons'] as $key => [$label, $default]) {
                $fields[] = Fieldset::make($label)
                    ->statePath("icons.{$group}.{$key}")
                    ->columns(2)
                    ->schema([
                        TextInput::make('text')
                            ->label('Símbolo')
                            ->placeholder($default ?? 'Ícono original')
                            ->maxLength(12)
                            ->helperText($default === null ? 'Vacío = ícono original. Puedes escribir un símbolo o emoji.' : "Vacío = {$default}"),
                        FileUpload::make('image')
                            ->label('O sube una imagen')
                            ->helperText('SVG, PNG o WebP. Reemplaza al símbolo.')
                            ->image()
                            ->acceptedFileTypes(['image/svg+xml', 'image/png', 'image/webp'])
                            ->maxSize(1024)
                            ->disk('public')
                            ->directory('site/icons')
                            ->visibility('public'),
                    ]);
            }

            $tabs[] = Tab::make($definition['label'])->schema($fields);
        }

        return $schema
            ->components([
                Form::make([Tabs::make('Íconos')->tabs($tabs)])
                    ->livewireSubmitHandler('save')
                    ->footer([
                        Actions::make([
                            Action::make('save')
                                ->label('Guardar cambios')
                                ->submit('save'),
                        ]),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        app(SiteIcons::class)->save($this->form->getState()['icons'] ?? []);

        Notification::make()->title('Cambios guardados')->success()->send();
    }

    /**
     * Shows the page where the icon being edited is seen; the blog's share icons live in the posts.
     */
    protected function previewPath(?string $statePath = null): string
    {
        preg_match('/icons\.(\w+)\.(\w+)/', (string) $statePath, $match);
        $group = $match[1] ?? 'general';

        if ($group === 'blog' && str_starts_with($match[2] ?? '', 'share_')) {
            return BlogPost::query()->published()->latest('published_at')->value('path') ?? '/blog';
        }

        return SiteIcons::GROUPS[$group]['url'] ?? '/';
    }
}
