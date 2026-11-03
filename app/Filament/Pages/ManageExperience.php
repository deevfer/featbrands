<?php

namespace App\Filament\Pages;

use App\Models\PageSection;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class ManageExperience extends Page
{
    protected string $view = 'filament.pages.manage-experience';

    protected static ?string $navigationLabel = 'Experience & Tech';

    protected static ?string $title = 'Administrar Experience & Tech';

    public ?array $data = [];

    private const SECTIONS = [
        'hero' => '1. Portada',
        'journey' => '2. Recorrido interactivo',
        'build' => '3. Qué construimos',
        'connect' => '4. Experiencias conectadas',
        'closing' => '5. Cierre',
        'seo' => '6. SEO',
    ];

    // ------------------------------------
    // CARGAR DATOS ORIGINALES Y DEL CMS
    // ------------------------------------

    public function mount(): void
    {
        $source = File::json(
            resource_path('data/specializations.json')
        );

        $records = PageSection::query()
            ->where('page', 'experience')
            ->get()
            ->keyBy('section');

        $state = [];

        foreach (['es', 'en'] as $lang) {
            $copy = $source[$lang]['experience'] ?? [];

            foreach (array_keys(self::SECTIONS) as $section) {
                $defaults = $this->sectionDefaults(
                    $copy,
                    $section
                );

                $saved = $records
                    ->get($section)
                    ?->getAttribute('content_' . $lang);

                $state["{$section}_{$lang}"] = array_replace(
                    $defaults,
                    is_array($saved) ? $saved : []
                );
            }
        }

        $this->form->fill($state);
    }

    // ------------------------------------
    // FORMULARIO PRINCIPAL
    // ------------------------------------

    public function form(Schema $schema): Schema
    {
        $tabs = [];

        foreach (self::SECTIONS as $section => $label) {
            $tabs[] = Tab::make($label)
                ->schema([
                    Tabs::make("Idiomas {$section}")
                        ->tabs([
                            Tab::make('Español')
                                ->schema(
                                    $this->sectionSchema(
                                        $section,
                                        'es'
                                    )
                                ),

                            Tab::make('Inglés')
                                ->schema(
                                    $this->sectionSchema(
                                        $section,
                                        'en'
                                    )
                                ),
                        ]),
                ]);
        }

        return $schema
            ->components([
                Form::make([
                    Tabs::make('Secciones de Experience & Tech')
                        ->tabs($tabs),
                ])
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

    // ------------------------------------
    // CAMPOS SEGÚN LA SECCIÓN
    // ------------------------------------

    private function sectionSchema(
        string $section,
        string $lang
    ): array {
        $s = "{$section}_{$lang}";

        return match ($section) {

            // --------------------------------
            // 1. PORTADA
            // --------------------------------

            'hero' => [
                Section::make('Encabezado principal')
                    ->description(
                        'Contenido de presentación de Experience & Tech.'
                    )
                    ->schema([
                        $this->textField(
                            "{$s}.name",
                            'Nombre de la especialización'
                        ),

                        $this->textField(
                            "{$s}.kicker",
                            'Texto inferior izquierdo'
                        ),

                        $this->textField(
                            "{$s}.title",
                            'Título principal'
                        ),

                        $this->textField(
                            "{$s}.accent",
                            'Texto destacado del título'
                        ),

                        $this->textareaField(
                            "{$s}.intro",
                            'Descripción introductoria',
                            5
                        ),
                    ]),

                Section::make('Botón principal')
                    ->description(
                        'Se conserva el diseño original del botón.'
                    )
                    ->schema([
                        $this->textField(
                            "{$s}.cta",
                            'Texto del botón'
                        ),

                        $this->textField(
                            "{$s}.ctaHref",
                            'Enlace del botón'
                        )
                            ->helperText(
                                'Ejemplo: /contacto. Al conectar ' .
                                'el frontend conservaremos idioma y mercado.'
                            ),
                    ])
                    ->columns(2),

                Section::make('Ilustración orbital')
                    ->description(
                        'Edita únicamente los textos. Los anillos, ' .
                        'colores, cursor y animaciones no se modificarán.'
                    )
                    ->schema([
                        $this->textareaField(
                            "{$s}.orbitCenter",
                            'Texto central de la ilustración',
                            3
                        )
                            ->helperText(
                                'Puedes utilizar saltos de línea.'
                            ),

                        Repeater::make("{$s}.orbitWords")
                            ->label('Tres etiquetas orbitales')
                            ->schema([
                                $this->textField(
                                    'word',
                                    'Texto de la etiqueta'
                                ),
                            ])
                            ->itemLabel(
                                fn (array $state): ?string =>
                                    $state['word'] ?? 'Etiqueta'
                            )
                            ->minItems(3)
                            ->maxItems(3)
                            ->addable(false)
                            ->deletable(false)
                            ->reorderable(false),
                    ]),

                Section::make('Texto inferior derecho')
                    ->schema([
                        $this->textField(
                            "{$s}.coverFootRight",
                            'Disciplinas mostradas'
                        )
                            ->helperText(
                                'Texto original: UX / UI / E-commerce / Tech'
                            ),
                    ]),
            ],

            // --------------------------------
            // 2. RECORRIDO INTERACTIVO
            // --------------------------------

            'journey' => [
                Section::make('Encabezado del recorrido')
                    ->description(
                        'Título y descripción que aparecen ' .
                        'encima del recorrido interactivo.'
                    )
                    ->schema([
                        $this->textField(
                            "{$s}.journeyKicker",
                            'Texto superior'
                        ),

                        $this->textareaField(
                            "{$s}.journeyTitle",
                            'Título del recorrido',
                            3
                        ),

                        $this->textareaField(
                            "{$s}.journeyBody",
                            'Descripción del recorrido',
                            5
                        ),
                    ]),

                Section::make('Navegador de demostración')
                    ->description(
                        'Edita el rótulo de la ventana simulada.'
                    )
                    ->schema([
                        $this->textField(
                            "{$s}.browserLabel",
                            'Texto de la barra superior'
                        ),
                    ]),

                Section::make('Tres etapas del recorrido')
                    ->description(
                        'Mantendremos exactamente tres etapas para ' .
                        'preservar las pestañas, paneles y animaciones ' .
                        'del JavaScript original.'
                    )
                    ->schema([
                        Repeater::make("{$s}.steps")
                            ->label('Etapas')
                            ->schema([
                                $this->textField(
                                    'label',
                                    'Nombre de la etapa'
                                ),

                                $this->textField(
                                    'title',
                                    'Título de la explicación'
                                ),

                                $this->textareaField(
                                    'body',
                                    'Descripción de la etapa',
                                    4
                                ),

                                $this->textField(
                                    'screenKicker',
                                    'Texto superior de la pantalla'
                                ),

                                $this->textareaField(
                                    'screenTitle',
                                    'Título dentro de la pantalla',
                                    3
                                )
                                    ->helperText(
                                        'Se conservan los saltos de línea.'
                                    ),

                                $this->textField(
                                    'screenAction',
                                    'Texto del botón de la pantalla'
                                ),

                                $this->textareaField(
                                    'screenNote',
                                    'Nota inferior de la pantalla',
                                    3
                                ),
                            ])
                            ->itemLabel(
                                fn (array $state): ?string =>
                                    $state['label']
                                    ?? 'Etapa del recorrido'
                            )
                            ->collapsed()
                            ->minItems(3)
                            ->maxItems(3)
                            ->addable(false)
                            ->deletable(false)
                            ->reorderable(false),
                    ]),

                Section::make('Controles del recorrido')
                    ->description(
                        'Textos utilizados por los botones ' .
                        'de siguiente etapa y reinicio.'
                    )
                    ->schema([
                        $this->textField(
                            "{$s}.nextStep",
                            'Texto de siguiente etapa'
                        ),

                        $this->textField(
                            "{$s}.restart",
                            'Texto para reiniciar'
                        ),

                        $this->textField(
                            "{$s}.progressLabel",
                            'Indicador visual de etapas'
                        )
                            ->helperText(
                                'Texto original: 01 → 02 → 03'
                            ),
                    ]),
            ],

            // --------------------------------
            // 3. QUÉ DISEÑAMOS Y CONSTRUIMOS
            // --------------------------------

            'build' => [
                Section::make('Encabezado')
                    ->schema([
                        $this->textField(
                            "{$s}.buildKicker",
                            'Texto superior'
                        ),

                        $this->textareaField(
                            "{$s}.buildTitle",
                            'Título principal de la sección',
                            3
                        )
                            ->helperText(
                                'Los saltos de línea se conservan.'
                            ),
                    ]),

                Section::make('Disciplinas y servicios')
                    ->description(
                        'Administra los cuatro elementos que ' .
                        'aparecen en el listado original.'
                    )
                    ->schema([
                        Repeater::make("{$s}.build")
                            ->label('Disciplinas')
                            ->schema([
                                $this->textField(
                                    'title',
                                    'Nombre de la disciplina'
                                ),

                                $this->textareaField(
                                    'description',
                                    'Descripción',
                                    4
                                ),

                                $this->textField(
                                    'tags',
                                    'Etiquetas de la disciplina'
                                )
                                    ->helperText(
                                        'Ejemplo: Research / Arquitectura / Prototipos'
                                    ),
                            ])
                            ->itemLabel(
                                fn (array $state): ?string =>
                                    $state['title']
                                    ?? 'Disciplina'
                            )
                            ->collapsed()
                            ->minItems(4)
                            ->maxItems(4)
                            ->addable(false)
                            ->deletable(false)
                            ->reorderable(false),
                    ]),
            ],

            // --------------------------------
            // 4. EXPERIENCIAS CONECTADAS
            // --------------------------------

            'connect' => [
                Section::make('Textos de la sección')
                    ->schema([
                        $this->textareaField(
                            "{$s}.connectTitle",
                            'Título principal',
                            3
                        ),

                        $this->textareaField(
                            "{$s}.connectBody",
                            'Descripción',
                            5
                        ),
                    ]),

                Section::make('Diagrama de conexión')
                    ->description(
                        'Se conservarán los tres nodos, sus ' .
                        'símbolos, colores y líneas conectoras.'
                    )
                    ->schema([
                        Repeater::make("{$s}.connectLabels")
                            ->label('Textos de los tres nodos')
                            ->schema([
                                $this->textField(
                                    'label',
                                    'Texto del nodo'
                                ),
                            ])
                            ->itemLabel(
                                fn (array $state): ?string =>
                                    $state['label'] ?? 'Nodo'
                            )
                            ->minItems(3)
                            ->maxItems(3)
                            ->addable(false)
                            ->deletable(false)
                            ->reorderable(false),
                    ]),
            ],

            // --------------------------------
            // 5. CIERRE
            // --------------------------------

            'closing' => [
                Section::make('Mensaje de cierre')
                    ->schema([
                        $this->textareaField(
                            "{$s}.closing",
                            'Mensaje principal',
                            5
                        ),
                    ]),

                Section::make('Botón de contacto')
                    ->schema([
                        $this->textField(
                            "{$s}.closingCta",
                            'Texto del botón'
                        ),

                        $this->textField(
                            "{$s}.closingHref",
                            'Enlace del botón'
                        )
                            ->helperText(
                                'Ejemplo: /contacto'
                            ),
                    ])
                    ->columns(2),

                Section::make('Siguiente especialización')
                    ->schema([
                        $this->textField(
                            "{$s}.next",
                            'Texto introductorio'
                        ),

                        $this->textField(
                            "{$s}.nextName",
                            'Nombre del siguiente servicio'
                        ),

                        $this->textField(
                            "{$s}.nextHref",
                            'Enlace del siguiente servicio'
                        )
                            ->helperText(
                                'Originalmente apunta a Performance & Connections.'
                            ),
                    ]),
            ],

            // --------------------------------
            // 6. SEO Y LINKEDIN
            // --------------------------------

            'seo' => [
                Section::make('SEO básico')
                    ->description(
                        'Metadatos de Experience & Tech.'
                    )
                    ->schema([
                        $this->textField(
                            "{$s}.meta_title",
                            'Metatítulo'
                        )
                            ->maxLength(255)
                            ->helperText(
                                'Recomendación: aproximadamente ' .
                                '50 a 60 caracteres.'
                            ),

                        $this->textareaField(
                            "{$s}.meta_description",
                            'Metadescripción',
                            4
                        )
                            ->helperText(
                                'Recomendación: aproximadamente ' .
                                '140 a 160 caracteres.'
                            ),

                        TextInput::make("{$s}.canonical_url")
                            ->label('URL canónica')
                            ->url()
                            ->helperText(
                                'Opcional. Utiliza una URL absoluta ' .
                                'con https://. Si queda vacía, ' .
                                'se generará desde la ruta de Experience.'
                            ),
                    ]),

                Section::make('LinkedIn y redes sociales')
                    ->description(
                        'Metadatos Open Graph para compartir ' .
                        'la página.'
                    )
                    ->schema([
                        TextInput::make("{$s}.og_title")
                            ->label('Título para redes sociales')
                            ->maxLength(255)
                            ->helperText(
                                'Opcional. Si queda vacío, ' .
                                'se utilizará el metatítulo.'
                            ),

                        Textarea::make("{$s}.og_description")
                            ->label('Descripción para redes sociales')
                            ->rows(4)
                            ->helperText(
                                'Opcional. Si queda vacía, ' .
                                'se utilizará la metadescripción.'
                            ),

                        FileUpload::make("{$s}.og_image")
                            ->label('Imagen para redes sociales')
                            ->image()
                            ->acceptedFileTypes([
                                'image/jpeg',
                                'image/png',
                                'image/webp',
                            ])
                            ->disk('public')
                            ->directory(
                                "experience/seo/{$lang}"
                            )
                            ->visibility('public')
                            ->maxSize(4096)
                            ->helperText(
                                'Recomendación: 1200 × 630 píxeles. ' .
                                'JPG, PNG o WebP. Máximo 4 MB.'
                            ),

                        TextInput::make("{$s}.og_image_alt")
                            ->label('Texto alternativo de la imagen'),
                    ]),

                Section::make('Indexación')
                    ->schema([
                        Toggle::make("{$s}.robots_index")
                            ->label('Permitir indexación')
                            ->default(true),

                        Toggle::make("{$s}.robots_follow")
                            ->label('Permitir seguimiento de enlaces')
                            ->default(true),
                    ])
                    ->columns(2),
            ],

            default => [],
        };
    }

    // ------------------------------------
    // COMPONENTES DE TEXTO REUTILIZABLES
    // ------------------------------------

    private function textField(
        string $name,
        string $label
    ): TextInput {
        return TextInput::make($name)
            ->label($label)
            ->required();
    }

    private function textareaField(
        string $name,
        string $label,
        int $rows = 3
    ): Textarea {
        return Textarea::make($name)
            ->label($label)
            ->rows($rows)
            ->required();
    }

    // ------------------------------------
    // VALORES PREDETERMINADOS
    // ------------------------------------

    private function sectionDefaults(
        array $copy,
        string $section
    ): array {
        return match ($section) {

            'hero' => [
                ...$this->pick($copy, [
                    'name',
                    'title',
                    'accent',
                    'intro',
                    'cta',
                    'kicker',
                    'orbitCenter',
                ]),

                'ctaHref' => route(
                    'contact',
                    [],
                    false
                ),

                'orbitWords' => array_map(
                    static fn ($word): array => [
                        'word' => (string) $word,
                    ],
                    $copy['orbitWords'] ?? []
                ),

                'coverFootRight' =>
                    'UX / UI / E-commerce / Tech',
            ],

            'journey' => [
                ...$this->pick($copy, [
                    'journeyKicker',
                    'journeyTitle',
                    'journeyBody',
                    'nextStep',
                    'restart',
                ]),

                'browserLabel' =>
                    'feat. / experience design',

                'progressLabel' =>
                    '01 → 02 → 03',

                'steps' => array_values(
                    $copy['steps'] ?? []
                ),
            ],

            'build' => [
                ...$this->pick($copy, [
                    'buildKicker',
                    'buildTitle',
                ]),

                'build' => array_map(
                    static fn (array $item): array => [
                        'title' => (string) (
                            $item[0] ?? ''
                        ),

                        'description' => (string) (
                            $item[1] ?? ''
                        ),

                        'tags' => (string) (
                            $item[2] ?? ''
                        ),
                    ],
                    $copy['build'] ?? []
                ),
            ],

            'connect' => [
                ...$this->pick($copy, [
                    'connectTitle',
                    'connectBody',
                ]),

                'connectLabels' => array_map(
                    static fn ($label): array => [
                        'label' => (string) $label,
                    ],
                    $copy['connectLabels'] ?? []
                ),
            ],

            'closing' => [
                ...$this->pick($copy, [
                    'closing',
                    'closingCta',
                    'next',
                    'nextName',
                ]),

                'closingHref' => route(
                    'contact',
                    [],
                    false
                ),

                'nextHref' => route(
                    'performance',
                    [],
                    false
                ),
            ],

            'seo' => [
                'meta_title' =>
                    'Experience & Tech — Feat.',

                'meta_description' =>
                    $copy['intro'] ?? '',

                'canonical_url' => '',

                'og_title' => '',
                'og_description' => '',
                'og_image' => null,
                'og_image_alt' => '',

                'robots_index' => true,
                'robots_follow' => true,
            ],

            default => [],
        };
    }

    private function pick(
        array $source,
        array $keys
    ): array {
        $result = [];

        foreach ($keys as $key) {
            $result[$key] = $source[$key] ?? '';
        }

        return $result;
    }

    // ------------------------------------
    // GUARDAR TODAS LAS SECCIONES
    // ------------------------------------

    public function save(): void
    {
        $state = $this->form->getState();

        DB::transaction(function () use ($state) {
            foreach (array_keys(self::SECTIONS) as $section) {
                $record = PageSection::firstOrNew([
                    'page' => 'experience',
                    'section' => $section,
                ]);

                foreach (['es', 'en'] as $lang) {
                    $column = 'content_' . $lang;

                    $previous = $record->{$column} ?? [];

                    $submitted = $state[
                        "{$section}_{$lang}"
                    ] ?? [];

                    $record->{$column} = array_replace(
                        is_array($previous) ? $previous : [],
                        $submitted
                    );
                }

                $record->save();
            }
        });

        Notification::make()
            ->title(
                'Experience & Tech actualizado correctamente'
            )
            ->success()
            ->send();
    }
}
