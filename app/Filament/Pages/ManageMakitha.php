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

class ManageMakitha extends Page
{
    protected string $view = 'filament.pages.manage-makitha';

    protected static ?string $navigationLabel = 'Makitha';

    protected static ?string $title = 'Administrar Makitha';

    public ?array $data = [];

    private const SECTIONS = [
        'hero' => '1. Portada',
        'solutions' => '2. Soluciones',
        'process' => '3. Proceso',
        'bridge' => '4. Makitha + Feat.',
        'closing' => '5. Cierre',
        'seo' => '6. SEO',
    ];

    // ------------------------------------
    // CARGAR CONTENIDO ORIGINAL Y DEL CMS
    // ------------------------------------

    public function mount(): void
    {
        $source = File::json(
            resource_path('data/specializations.json')
        );

        $records = PageSection::query()
            ->where('page', 'makitha')
            ->get()
            ->keyBy('section');

        $state = [];

        foreach (['es', 'en'] as $lang) {
            $copy = $source[$lang]['makitha'] ?? [];

            foreach (array_keys(self::SECTIONS) as $section) {
                $defaults = $this->sectionDefaults(
                    $copy,
                    $section,
                    $lang
                );

                $saved = $records
                    ->get($section)
                    ?->getAttribute('content_' . $lang);

                $content = array_replace(
                    $defaults,
                    is_array($saved) ? $saved : []
                );

                // El formulario muestra un solo título.
                // Reconstruimos el texto completo usando
                // los valores que ya están guardados.
                if ($section === 'hero') {
                    $content['fullTitle'] = trim(
                        (string) ($content['title'] ?? '')
                        . ' '
                        . (string) ($content['accent'] ?? '')
                    );
                }

                $state["{$section}_{$lang}"] = $content;
            }
        }

        $this->form->fill($state);
    }

    // ------------------------------------
    // FORMULARIO GENERAL
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
                    Tabs::make('Secciones de Makitha')
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
    // CAMPOS POR SECCIÓN
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
                Section::make('Información superior')
                    ->description(
                        'Textos de la cabecera de Makitha. ' .
                        'La navegación y el diseño se conservan.'
                    )
                    ->schema([
                        $this->text(
                            "{$s}.name",
                            'Nombre de la especialización'
                        ),

                        $this->text(
                            "{$s}.kicker",
                            'Texto superior central'
                        ),

                        $this->text(
                            "{$s}.mastheadRight",
                            'Texto superior derecho'
                        )
                            ->helperText(
                                'Original: MAKITHA / FEAT.'
                            ),

                        $this->text(
                            "{$s}.heroEyebrow",
                            'Etiqueta encima del título'
                        )
                            ->helperText(
                                'Original: Makitha / Tech Growth Partner'
                            ),
                    ]),

                Section::make('Título y presentación')
                    ->schema([
                        // CAMBIO: un único campo de título.
                        $this->area(
                            "{$s}.fullTitle",
                            'Título completo de la portada',
                            3
                        )
                            ->helperText(
                                'Escribe el título completo. ' .
                                'La última palabra conservará ' .
                                'el estilo destacado original.'
                            ),

                        $this->area(
                            "{$s}.intro",
                            'Descripción introductoria',
                            5
                        ),
                    ]),

                Section::make('Botones de portada')
                    ->description(
                        'El botón de explorar seguirá ' .
                        'desplazándose hasta la sección ' .
                        'de soluciones.'
                    )
                    ->schema([
                        $this->text(
                            "{$s}.cta",
                            'Texto del botón principal'
                        ),

                        $this->text(
                            "{$s}.ctaHref",
                            'Enlace del botón principal'
                        )
                            ->helperText(
                                'Ejemplo: /contacto'
                            ),

                        $this->text(
                            "{$s}.explore",
                            'Texto del enlace Explorar soluciones'
                        ),
                    ]),

                Section::make('Motor de crecimiento')
                    ->description(
                        'Administra sus textos. La imagen, ' .
                        'sus cuatro posiciones, colores, ' .
                        'símbolos y líneas permanecerán intactos.'
                    )
                    ->schema([
                        $this->text(
                            "{$s}.engineTagline",
                            'Texto superior derecho del motor'
                        )
                            ->helperText(
                                'Original: tech + strategy'
                            ),

                        $this->text(
                            "{$s}.engineTitle",
                            'Título descriptivo del motor'
                        )
                            ->helperText(
                                'También se utiliza como ' .
                                'etiqueta accesible.'
                            ),

                        $this->text(
                            "{$s}.engineCenter",
                            'Texto central del motor'
                        ),

                        Repeater::make("{$s}.engineNodes")
                            ->label('Cuatro nodos del motor')
                            ->schema([
                                $this->text(
                                    'text',
                                    'Texto del nodo'
                                ),
                            ])
                            ->itemLabel(
                                fn (array $state): ?string =>
                                    $state['text'] ?? 'Nodo'
                            )
                            ->minItems(4)
                            ->maxItems(4)
                            ->addable(false)
                            ->deletable(false)
                            ->reorderable(false),

                        $this->area(
                            "{$s}.engineNote",
                            'Nota inferior izquierda',
                            3
                        ),

                        $this->text(
                            "{$s}.engineFoot",
                            'Texto inferior derecho'
                        ),
                    ]),
            ],

            // --------------------------------
            // 2. SOLUCIONES
            // --------------------------------

            'solutions' => [
                Section::make('Encabezado de soluciones')
                    ->schema([
                        $this->text(
                            "{$s}.solutionsKicker",
                            'Texto superior'
                        ),

                        $this->area(
                            "{$s}.solutionsTitle",
                            'Título principal',
                            3
                        )
                            ->helperText(
                                'Se conservarán los saltos de línea.'
                            ),

                        $this->area(
                            "{$s}.solutionsIntro",
                            'Descripción de la sección',
                            5
                        ),
                    ]),

                Section::make('Seis soluciones de Makitha')
                    ->description(
                        'Edita todos los textos de cada solución. ' .
                        'Las seis pestañas, sus posiciones y las ' .
                        'ilustraciones individuales se conservarán.'
                    )
                    ->schema([
                        Repeater::make("{$s}.solutions")
                            ->label('Soluciones')
                            ->schema([
                                $this->text(
                                    'title',
                                    'Nombre completo de la solución'
                                ),

                                $this->text(
                                    'short',
                                    'Nombre corto de la pestaña'
                                ),

                                $this->text(
                                    'headline',
                                    'Título destacado del panel'
                                ),

                                $this->area(
                                    'body',
                                    'Descripción del panel',
                                    5
                                ),

                                $this->text(
                                    'word',
                                    'Texto de la ilustración'
                                )
                                    ->helperText(
                                        'Ejemplo: Web → Acción'
                                    ),

                                Repeater::make('outputs')
                                    ->label(
                                        'Tres etiquetas de la solución'
                                    )
                                    ->schema([
                                        $this->text(
                                            'text',
                                            'Texto de la etiqueta'
                                        ),
                                    ])
                                    ->itemLabel(
                                        fn (array $state): ?string =>
                                            $state['text']
                                            ?? 'Etiqueta'
                                    )
                                    ->minItems(3)
                                    ->maxItems(3)
                                    ->addable(false)
                                    ->deletable(false)
                                    ->reorderable(false),
                            ])
                            ->itemLabel(
                                fn (array $state): ?string =>
                                    $state['short']
                                    ?? 'Solución'
                            )
                            ->collapsed()
                            ->minItems(6)
                            ->maxItems(6)
                            ->addable(false)
                            ->deletable(false)
                            ->reorderable(false),
                    ]),
            ],

            // --------------------------------
            // 3. PROCESO
            // --------------------------------

            'process' => [
                Section::make('Encabezado del proceso')
                    ->schema([
                        $this->text(
                            "{$s}.processKicker",
                            'Texto superior'
                        ),

                        $this->area(
                            "{$s}.processTitle",
                            'Título principal',
                            3
                        )
                            ->helperText(
                                'Se conservarán los saltos de línea.'
                            ),
                    ]),

                Section::make('Cuatro etapas del proceso')
                    ->description(
                        'Se mantiene el orden original ' .
                        'para conservar la presentación visual ' .
                        'de las cuatro columnas.'
                    )
                    ->schema([
                        Repeater::make("{$s}.process")
                            ->label('Etapas')
                            ->schema([
                                $this->text(
                                    'title',
                                    'Nombre de la etapa'
                                ),

                                $this->area(
                                    'description',
                                    'Descripción de la etapa',
                                    4
                                ),
                            ])
                            ->itemLabel(
                                fn (array $state): ?string =>
                                    $state['title']
                                    ?? 'Etapa'
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
            // 4. MAKITHA + FEAT.
            // --------------------------------

            'bridge' => [
                Section::make('Contenido de la sección')
                    ->description(
                        'Administra los textos de presentación ' .
                        'de Makitha como unidad de Feat. ' .
                        'Los logotipos originales no se modificarán.'
                    )
                    ->schema([
                        $this->area(
                            "{$s}.bridgeTitle",
                            'Título principal',
                            4
                        )
                            ->helperText(
                                'Se conservarán los saltos de línea.'
                            ),

                        $this->area(
                            "{$s}.bridgeBody",
                            'Descripción',
                            6
                        ),
                    ]),

                Section::make('Enlace al sitio de Makitha')
                    ->schema([
                        $this->text(
                            "{$s}.websiteCta",
                            'Texto del enlace'
                        ),

                        TextInput::make("{$s}.websiteHref")
                            ->label('URL del sitio de Makitha')
                            ->url()
                            ->required()
                            ->helperText(
                                'Enlace completo con https://. ' .
                                'El valor original incluye ' .
                                'el idioma correspondiente.'
                            ),
                    ]),
            ],

            // --------------------------------
            // 5. CIERRE
            // --------------------------------

            'closing' => [
                Section::make('Contenido del cierre')
                    ->schema([
                        $this->text(
                            "{$s}.closingEyebrow",
                            'Texto superior'
                        )
                            ->helperText(
                                'Original: Intelligence for scalable growth.'
                            ),

                        $this->area(
                            "{$s}.closing",
                            'Título principal',
                            5
                        )
                            ->helperText(
                                'Se mantendrán los saltos de línea.'
                            ),
                    ]),

                Section::make('Botón principal')
                    ->schema([
                        $this->text(
                            "{$s}.closingCta",
                            'Texto del botón'
                        ),

                        $this->text(
                            "{$s}.closingHref",
                            'Enlace del botón'
                        )
                            ->helperText(
                                'Ejemplo: /contacto'
                            ),
                    ])
                    ->columns(2),

                Section::make('Siguiente especialización')
                    ->description(
                        'Enlace inferior hacia Experience & Tech.'
                    )
                    ->schema([
                        $this->text(
                            "{$s}.next",
                            'Texto introductorio'
                        ),

                        $this->text(
                            "{$s}.nextName",
                            'Nombre de la especialización'
                        ),

                        $this->text(
                            "{$s}.nextHref",
                            'Enlace de la especialización'
                        )
                            ->helperText(
                                'Originalmente apunta a Experience & Tech.'
                            ),
                    ]),
            ],

            // --------------------------------
            // 6. SEO Y LINKEDIN
            // --------------------------------

            'seo' => [
                Section::make('SEO básico')
                    ->description(
                        'Metadatos para los motores de búsqueda.'
                    )
                    ->schema([
                        $this->text(
                            "{$s}.meta_title",
                            'Metatítulo'
                        )
                            ->maxLength(255)
                            ->helperText(
                                'Como referencia, se recomiendan ' .
                                'aproximadamente 50 a 60 caracteres.'
                            ),

                        $this->area(
                            "{$s}.meta_description",
                            'Metadescripción',
                            4
                        )
                            ->helperText(
                                'Como referencia, se recomiendan ' .
                                'aproximadamente 140 a 160 caracteres.'
                            ),

                        TextInput::make("{$s}.canonical_url")
                            ->label('URL canónica')
                            ->url()
                            ->helperText(
                                'Opcional. Si está vacía, ' .
                                'se generará desde la ruta de Makitha.'
                            ),
                    ]),

                Section::make(
                    'LinkedIn y redes sociales (Open Graph)'
                )
                    ->description(
                        'Controla cómo aparece Makitha ' .
                        'al compartirse en redes sociales.'
                    )
                    ->schema([
                        TextInput::make("{$s}.og_title")
                            ->label('Título para redes sociales')
                            ->maxLength(255)
                            ->helperText(
                                'Opcional. Utiliza el metatítulo ' .
                                'cuando está vacío.'
                            ),

                        Textarea::make("{$s}.og_description")
                            ->label('Descripción para redes sociales')
                            ->rows(4)
                            ->helperText(
                                'Opcional. Utiliza la metadescripción ' .
                                'cuando está vacía.'
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
                                "makitha/seo/{$lang}"
                            )
                            ->visibility('public')
                            ->maxSize(4096)
                            ->helperText(
                                'Recomendado: 1200 × 630 píxeles. ' .
                                'JPG, PNG o WebP. Máximo 4 MB.'
                            ),

                        TextInput::make("{$s}.og_image_alt")
                            ->label(
                                'Texto alternativo de la imagen'
                            ),
                    ]),

                Section::make('Indexación')
                    ->schema([
                        Toggle::make("{$s}.robots_index")
                            ->label('Permitir indexación')
                            ->default(true),

                        Toggle::make("{$s}.robots_follow")
                            ->label(
                                'Permitir seguimiento de enlaces'
                            )
                            ->default(true),
                    ])
                    ->columns(2),
            ],

            default => [],
        };
    }

    // ------------------------------------
    // COMPONENTES REUTILIZABLES
    // ------------------------------------

    private function text(
        string $name,
        string $label
    ): TextInput {
        return TextInput::make($name)
            ->label($label)
            ->required();
    }

    private function area(
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
    // VALORES INICIALES
    // ------------------------------------

    private function sectionDefaults(
        array $copy,
        string $section,
        string $lang
    ): array {
        return match ($section) {

            'hero' => [
                ...$this->pick($copy, [
                    'name',
                    'kicker',
                    'title',
                    'accent',
                    'intro',
                    'cta',
                    'explore',
                    'engineTitle',
                    'engineCenter',
                    'engineNote',
                    'engineFoot',
                ]),

                // El campo visible reúne ambos textos.
                'fullTitle' => trim(
                    (string) ($copy['title'] ?? '')
                    . ' '
                    . (string) ($copy['accent'] ?? '')
                ),

                'mastheadRight' => 'MAKITHA / FEAT.',

                'heroEyebrow' =>
                    'Makitha / Tech Growth Partner',

                'engineTagline' => 'tech + strategy',

                'ctaHref' => route(
                    'contact',
                    [],
                    false
                ),

                'engineNodes' => array_values(
                    array_map(
                        static fn ($node): array => [
                            'text' => (string) $node,
                        ],
                        $copy['engineNodes'] ?? []
                    )
                ),
            ],

            'solutions' => [
                ...$this->pick($copy, [
                    'solutionsKicker',
                    'solutionsTitle',
                    'solutionsIntro',
                ]),

                'solutions' => array_values(
                    array_map(
                        static function (array $solution): array {
                            return [
                                'title' =>
                                    $solution['title'] ?? '',

                                'short' =>
                                    $solution['short'] ?? '',

                                'headline' =>
                                    $solution['headline'] ?? '',

                                'body' =>
                                    $solution['body'] ?? '',

                                'word' =>
                                    $solution['word'] ?? '',

                                'outputs' => array_values(
                                    array_map(
                                        static fn ($output): array => [
                                            'text' => (string) $output,
                                        ],
                                        $solution['outputs'] ?? []
                                    )
                                ),
                            ];
                        },
                        $copy['solutions'] ?? []
                    )
                ),
            ],

            'process' => [
                ...$this->pick($copy, [
                    'processKicker',
                    'processTitle',
                ]),

                'process' => array_values(
                    array_map(
                        static fn (array $step): array => [
                            'title' =>
                                (string) ($step[0] ?? ''),

                            'description' =>
                                (string) ($step[1] ?? ''),
                        ],
                        $copy['process'] ?? []
                    )
                ),
            ],

            'bridge' => [
                ...$this->pick($copy, [
                    'bridgeTitle',
                    'bridgeBody',
                    'websiteCta',
                ]),

                'websiteHref' =>
                    'https://www.makitha.com/?lang='
                    . $lang,
            ],

            'closing' => [
                ...$this->pick($copy, [
                    'closing',
                    'closingCta',
                    'next',
                    'nextName',
                ]),

                'closingEyebrow' =>
                    'Intelligence for scalable growth.',

                'closingHref' => route(
                    'contact',
                    [],
                    false
                ),

                'nextHref' => route(
                    'experience',
                    [],
                    false
                ),
            ],

            'seo' => [
                'meta_title' => 'Makitha — Feat.',

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
                    'page' => 'makitha',
                    'section' => $section,
                ]);

                foreach (['es', 'en'] as $lang) {
                    $column = 'content_' . $lang;

                    $previous = $record->{$column} ?? [];

                    $submitted = $state[
                        "{$section}_{$lang}"
                    ] ?? [];

                    // ------------------------------------
                    // TÍTULO ÚNICO DE LA PORTADA
                    // ------------------------------------

                    if ($section === 'hero') {
                        $fullTitle = preg_replace(
                            '/\s+/u',
                            ' ',
                            trim((string) (
                                $submitted['fullTitle'] ?? ''
                            ))
                        );

                        $submitted['fullTitle'] = $fullTitle;

                        // Separamos solamente la última palabra
                        // para conservar su estilo original.
                        if (preg_match(
                            '/^(.+)\s+(\S+)$/u',
                            $fullTitle,
                            $parts
                        )) {
                            $submitted['title'] = $parts[1];
                            $submitted['accent'] = $parts[2];
                        } else {
                            $submitted['title'] = '';
                            $submitted['accent'] = $fullTitle;
                        }
                    }

                    // ------------------------------------
                    // GUARDADO DEL CONTENIDO
                    // ------------------------------------

                    $record->{$column} = array_replace(
                        is_array($previous) ? $previous : [],
                        $submitted
                    );
                }

                $record->save();
            }
        });

        Notification::make()
            ->title('Makitha actualizado correctamente')
            ->success()
            ->send();
    }
}
