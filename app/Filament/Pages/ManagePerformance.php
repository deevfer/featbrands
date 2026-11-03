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
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class ManagePerformance extends Page
{
    protected string $view = 'filament.pages.manage-performance';

    protected static ?string $navigationLabel = 'Performance & Connections';

    protected static ?string $title = 'Administrar Performance & Connections';

    public ?array $data = [];

    private const SECTIONS = [
        'hero',
        'problem',
        'capabilities',
        'process',
        'cases',
        'faq',
        'cta',
        'related',
        'seo',
    ];

    // ------------------------------------
    // CARGAR DATOS
    // ------------------------------------

    public function mount(): void
    {
        $site = File::json(resource_path('data/site.json'));

        $records = PageSection::query()
            ->where('page', 'performance')
            ->get()
            ->keyBy('section');

        $state = [];

        foreach (['es', 'en'] as $lang) {
            foreach (self::SECTIONS as $section) {
                $defaults = $this->sectionDefaults(
                    $site[$lang],
                    $section,
                    $lang
                );

                $saved = $records
                    ->get($section)
                    ?->getAttribute('content_' . $lang) ?? [];

                $state[$section . '_' . $lang] = array_replace(
                    $defaults,
                    is_array($saved) ? $saved : []
                );
            }
        }

        $this->form->fill($state);
    }

    // ------------------------------------
    // FORMULARIO GENERAL
    // ------------------------------------

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Form::make([
                    Tabs::make('Secciones de Performance')
                        ->tabs([
                            Tab::make('1. Encabezado')
                                ->schema([
                                    Tabs::make('Idiomas Encabezado')
                                        ->tabs([
                                            $this->heroTab('Español', 'es'),
                                            $this->heroTab('Inglés', 'en'),
                                        ]),
                                ]),

                            Tab::make('2. Problema')
                                ->schema([
                                    Tabs::make('Idiomas Problema')
                                        ->tabs([
                                            $this->problemTab('Español', 'es'),
                                            $this->problemTab('Inglés', 'en'),
                                        ]),
                                ]),

                            Tab::make('3. Capacidades')
                                ->schema([
                                    Tabs::make('Idiomas Capacidades')
                                        ->tabs([
                                            $this->capabilitiesTab('Español', 'es'),
                                            $this->capabilitiesTab('Inglés', 'en'),
                                        ]),
                                ]),

                            Tab::make('4. Proceso')
                                ->schema([
                                    Tabs::make('Idiomas Proceso')
                                        ->tabs([
                                            $this->processTab('Español', 'es'),
                                            $this->processTab('Inglés', 'en'),
                                        ]),
                                ]),

                            Tab::make('5. Resultados y casos')
                                ->schema([
                                    Tabs::make('Idiomas Resultados')
                                        ->tabs([
                                            $this->casesTab('Español', 'es'),
                                            $this->casesTab('Inglés', 'en'),
                                        ]),
                                ]),

                            Tab::make('6. Preguntas frecuentes')
                                ->schema([
                                    Tabs::make('Idiomas FAQ')
                                        ->tabs([
                                            $this->faqTab('Español', 'es'),
                                            $this->faqTab('Inglés', 'en'),
                                        ]),
                                ]),

                            Tab::make('7. Llamado a la acción')
                                ->schema([
                                    Tabs::make('Idiomas CTA')
                                        ->tabs([
                                            $this->ctaTab('Español', 'es'),
                                            $this->ctaTab('Inglés', 'en'),
                                        ]),
                                ]),

                            Tab::make('8. Servicios relacionados')
                                ->schema([
                                    Tabs::make('Idiomas Relacionados')
                                        ->tabs([
                                            $this->relatedTab('Español', 'es'),
                                            $this->relatedTab('Inglés', 'en'),
                                        ]),
                                ]),

                            Tab::make('9. SEO')
                                ->schema([
                                    Tabs::make('Idiomas SEO')
                                        ->tabs([
                                            $this->seoTab('Español', 'es'),
                                            $this->seoTab('Inglés', 'en'),
                                        ]),
                                ]),
                        ]),
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
    // SECCIÓN 1 - ENCABEZADO
    // ------------------------------------

    private function heroTab(string $label, string $lang): Tab
    {
        return Tab::make($label)
            ->schema([
                Section::make('Ruta de navegación')
                    ->description(
                        'Administra los textos de navegación ' .
                        'que aparecen encima del encabezado.'
                    )
                    ->schema([
                        TextInput::make("hero_{$lang}.crumbServices")
                            ->label('Texto de especializaciones')
                            ->required()
                            ->helperText(
                                'El enlace de esta opción ' .
                                'continuará apuntando a Home.'
                            ),

                        TextInput::make("hero_{$lang}.crumbCurrent")
                            ->label('Nombre de la página')
                            ->required(),
                    ])
                    ->columns(2),

                Section::make('Encabezado principal')
                    ->description(
                        'Configura el título y la información ' .
                        'de presentación de Performance & Connections.'
                    )
                    ->schema([
                        TextInput::make("hero_{$lang}.kicker")
                            ->label('Texto superior')
                            ->required(),

                        Textarea::make("hero_{$lang}.h1")
                            ->label('Título principal (H1)')
                            ->rows(2)
                            ->required(),

                        TextInput::make("hero_{$lang}.answerTag")
                            ->label('Etiqueta del recuadro')
                            ->required(),

                        Textarea::make("hero_{$lang}.answer")
                            ->label('Descripción destacada')
                            ->rows(5)
                            ->required(),
                    ]),
            ]);
    }

    // ------------------------------------
    // SECCIÓN 2 - PROBLEMA
    // ------------------------------------

    private function problemTab(string $label, string $lang): Tab
    {
        return Tab::make($label)
            ->schema([
                Section::make('Problema')
                    ->description(
                        'Administra el contenido de la sección ' .
                        'que describe el problema o desafío.'
                    )
                    ->schema([
                        TextInput::make("problem_{$lang}.problemH")
                            ->label('Encabezado de la sección')
                            ->required(),

                        Textarea::make("problem_{$lang}.problem")
                            ->label('Descripción del problema')
                            ->rows(6)
                            ->required(),
                    ]),
            ]);
    }

    // ------------------------------------
    // SECCIÓN 3 - CAPACIDADES
    // ------------------------------------

    private function capabilitiesTab(
        string $label,
        string $lang
    ): Tab {
        return Tab::make($label)
            ->schema([
                Section::make('Encabezado')
                    ->schema([
                        TextInput::make(
                            "capabilities_{$lang}.capsH"
                        )
                            ->label('Título de la sección')
                            ->required(),
                    ]),

                Section::make('Listado de capacidades')
                    ->description(
                        'Cada capacidad incluye un número, ' .
                        'un título y una descripción. ' .
                        'El sitio conserva el diseño original ' .
                        'de las filas.'
                    )
                    ->schema([
                        Repeater::make(
                            "capabilities_{$lang}.caps"
                        )
                            ->label('Capacidades')
                            ->schema([
                                TextInput::make('n')
                                    ->label('Número')
                                    ->helperText(
                                        'Ejemplo: 01'
                                    )
                                    ->required(),

                                TextInput::make('title')
                                    ->label('Título')
                                    ->required(),

                                Textarea::make('desc')
                                    ->label('Descripción')
                                    ->rows(3)
                                    ->required(),
                            ])
                            ->itemLabel(
                                fn (array $state): ?string =>
                                    $state['title']
                                    ?? 'Nueva capacidad'
                            )
                            ->collapsed()
                            ->minItems(1)
                            ->addActionLabel('Agregar capacidad')
                            ->addable()
                            ->deletable()
                            ->reorderable(),
                    ]),
            ]);
    }

    // ------------------------------------
    // SECCIÓN 4 - PROCESO
    // ------------------------------------

    private function processTab(string $label, string $lang): Tab
    {
        return Tab::make($label)
            ->schema([
                Section::make('Encabezado')
                    ->schema([
                        TextInput::make("process_{$lang}.processH")
                            ->label('Título de la sección')
                            ->required(),
                    ]),

                Section::make('Etapas del proceso')
                    ->description(
                        'Administra las etapas que se muestran ' .
                        'en las tarjetas del proceso.'
                    )
                    ->schema([
                        Repeater::make("process_{$lang}.process")
                            ->label('Etapas')
                            ->schema([
                                TextInput::make('n')
                                    ->label('Número de etapa')
                                    ->helperText('Ejemplo: 01')
                                    ->required(),

                                TextInput::make('title')
                                    ->label('Título')
                                    ->required(),

                                Textarea::make('desc')
                                    ->label('Descripción')
                                    ->rows(3)
                                    ->required(),
                            ])
                            ->itemLabel(
                                fn (array $state): ?string =>
                                    $state['title']
                                    ?? 'Nueva etapa'
                            )
                            ->collapsed()
                            ->minItems(1)
                            ->addActionLabel('Agregar etapa')
                            ->addable()
                            ->deletable()
                            ->reorderable(),
                    ]),
            ]);
    }

    // ------------------------------------
    // SECCIÓN 5 - RESULTADOS Y CASOS
    // ------------------------------------

    private function casesTab(string $label, string $lang): Tab
    {
        return Tab::make($label)
            ->schema([
                Section::make('Encabezado')
                    ->schema([
                        TextInput::make("cases_{$lang}.casesH")
                            ->label('Título de la sección')
                            ->required(),

                        TextInput::make("cases_{$lang}.demoTag")
                            ->label('Etiqueta de las tarjetas')
                            ->helperText(
                                'Texto que aparece en la parte superior ' .
                                'de cada caso. Se aplica únicamente ' .
                                'a Performance & Connections.'
                            )
                            ->required(),
                    ]),

                Section::make('Casos destacados')
                    ->description(
                        'Administra los clientes, resultados ' .
                        'y descripciones de las tarjetas.'
                    )
                    ->schema([
                        Repeater::make("cases_{$lang}.cards")
                            ->label('Casos')
                            ->schema([
                                TextInput::make('client')
                                    ->label('Cliente')
                                    ->required(),

                                TextInput::make('metric')
                                    ->label('Métrica destacada')
                                    ->helperText(
                                        'Ejemplo: +38% o 2.4×'
                                    )
                                    ->required(),

                                TextInput::make('metricLabel')
                                    ->label('Descripción de la métrica'),

                                Textarea::make('desc')
                                    ->label('Descripción del caso')
                                    ->rows(3),
                            ])
                            ->itemLabel(
                                fn (array $state): ?string =>
                                    $state['client']
                                    ?? 'Nuevo caso'
                            )
                            ->collapsed()
                            ->minItems(1)
                            ->addActionLabel('Agregar caso')
                            ->addable()
                            ->deletable()
                            ->reorderable(),
                    ]),
            ]);
    }

    // ------------------------------------
    // SECCIÓN 6 - PREGUNTAS FRECUENTES
    // ------------------------------------

    private function faqTab(string $label, string $lang): Tab
    {
        return Tab::make($label)
            ->schema([
                Section::make('Encabezado')
                    ->schema([
                        TextInput::make("faq_{$lang}.faqH")
                            ->label('Título de la sección')
                            ->required(),
                    ]),

                Section::make('Preguntas frecuentes')
                    ->description(
                        'Administra las preguntas y respuestas ' .
                        'del acordeón. Su funcionamiento se ' .
                        'mantendrá sin modificaciones.'
                    )
                    ->schema([
                        Repeater::make("faq_{$lang}.faq")
                            ->label('Preguntas')
                            ->schema([
                                TextInput::make('q')
                                    ->label('Pregunta')
                                    ->required(),

                                Textarea::make('a')
                                    ->label('Respuesta')
                                    ->rows(5)
                                    ->required(),
                            ])
                            ->itemLabel(
                                fn (array $state): ?string =>
                                    $state['q']
                                    ?? 'Nueva pregunta'
                            )
                            ->collapsed()
                            ->minItems(1)
                            ->addActionLabel('Agregar pregunta')
                            ->addable()
                            ->deletable()
                            ->reorderable(),
                    ]),
            ]);
    }

    // ------------------------------------
    // SECCIÓN 7 - LLAMADO A LA ACCIÓN
    // ------------------------------------

    private function ctaTab(string $label, string $lang): Tab
    {
        return Tab::make($label)
            ->schema([
                Section::make('Contenido principal')
                    ->description(
                        'Configura el contenido del recuadro ' .
                        'de llamado a la acción.'
                    )
                    ->schema([
                        Textarea::make("cta_{$lang}.ctaTitle")
                            ->label('Título principal')
                            ->rows(3)
                            ->required(),

                        Textarea::make("cta_{$lang}.ctaBody")
                            ->label('Descripción')
                            ->rows(4)
                            ->required(),
                    ]),

                Section::make('Botón')
                    ->description(
                        'Administra el texto y el enlace ' .
                        'del botón principal.'
                    )
                    ->schema([
                        TextInput::make("cta_{$lang}.ctaBtn")
                            ->label('Texto del botón')
                            ->required(),

                        TextInput::make("cta_{$lang}.ctaHref")
                            ->label('Enlace del botón')
                            ->required()
                            ->helperText(
                                'Ejemplo: /contacto. ' .
                                'Se conservarán el idioma y el mercado ' .
                                'al conectar el frontend.'
                            ),
                    ])
                    ->columns(2),
            ]);
    }

    // ------------------------------------
    // SECCIÓN 8 - SERVICIOS RELACIONADOS
    // ------------------------------------

    private function relatedTab(string $label, string $lang): Tab
    {
        return Tab::make($label)
            ->schema([
                Section::make('Tarjetas de servicios relacionados')
                    ->description(
                        'Administra las dos tarjetas finales. ' .
                        'Sus colores, posiciones y estilos ' .
                        'originales permanecerán intactos.'
                    )
                    ->schema([
                        Repeater::make("related_{$lang}.cards")
                            ->label('Servicios relacionados')
                            ->schema([
                                TextInput::make('tag')
                                    ->label('Etiqueta superior')
                                    ->required(),

                                TextInput::make('title')
                                    ->label('Título')
                                    ->required(),

                                Textarea::make('desc')
                                    ->label('Descripción')
                                    ->rows(3)
                                    ->required(),

                                TextInput::make('cta')
                                    ->label('Texto de acción')
                                    ->required(),

                                TextInput::make('href')
                                    ->label('Enlace de la tarjeta')
                                    ->required()
                                    ->helperText(
                                        'Ejemplo: /experience-tech'
                                    ),
                            ])
                            ->itemLabel(
                                fn (array $state): ?string =>
                                    $state['title']
                                    ?? 'Servicio relacionado'
                            )
                            ->collapsed()
                            ->minItems(2)
                            ->maxItems(2)
                            ->addable(false)
                            ->deletable(false)
                            ->reorderable(false),
                    ]),
            ]);
    }

    // ------------------------------------
    // SECCIÓN 9 - SEO Y LINKEDIN
    // ------------------------------------

    private function seoTab(string $label, string $lang): Tab
    {
        return Tab::make($label)
            ->schema([
                Section::make('SEO básico')
                    ->description(
                        'Configura los metadatos de la página ' .
                        'para los motores de búsqueda.'
                    )
                    ->schema([
                        TextInput::make("seo_{$lang}.meta_title")
                            ->label('Metatítulo')
                            ->required()
                            ->maxLength(255)
                            ->helperText(
                                'Se recomienda un título claro ' .
                                'de aproximadamente 50 a 60 caracteres.'
                            ),

                        Textarea::make(
                            "seo_{$lang}.meta_description"
                        )
                            ->label('Metadescripción')
                            ->rows(3)
                            ->required()
                            ->helperText(
                                'Como referencia, se recomiendan ' .
                                'aproximadamente 140 a 160 caracteres.'
                            ),

                        TextInput::make(
                            "seo_{$lang}.canonical_url"
                        )
                            ->label('URL canónica')
                            ->url()
                            ->helperText(
                                'Opcional. URL absoluta con https://. ' .
                                'Si está vacía, se podrá generar ' .
                                'automáticamente al conectar la vista.'
                            ),
                    ]),

                Section::make(
                    'LinkedIn y redes sociales (Open Graph)'
                )
                    ->description(
                        'Configura cómo se verá esta página ' .
                        'al compartirse en LinkedIn y otras redes.'
                    )
                    ->schema([
                        TextInput::make("seo_{$lang}.og_title")
                            ->label('Título para redes sociales')
                            ->maxLength(255)
                            ->helperText(
                                'Opcional. Si está vacío, ' .
                                'se utilizará el metatítulo.'
                            ),

                        Textarea::make(
                            "seo_{$lang}.og_description"
                        )
                            ->label('Descripción para redes sociales')
                            ->rows(3)
                            ->helperText(
                                'Opcional. Si está vacía, ' .
                                'se utilizará la metadescripción.'
                            ),

                        FileUpload::make("seo_{$lang}.og_image")
                            ->label('Imagen para redes sociales')
                            ->image()
                            ->acceptedFileTypes([
                                'image/jpeg',
                                'image/png',
                                'image/webp',
                            ])
                            ->disk('public')
                            ->directory(
                                "performance/seo/{$lang}"
                            )
                            ->visibility('public')
                            ->maxSize(4096)
                            ->helperText(
                                'Se recomienda 1200 × 630 píxeles. ' .
                                'JPG, PNG o WebP. Máximo 4 MB.'
                            ),

                        TextInput::make(
                            "seo_{$lang}.og_image_alt"
                        )
                            ->label('Texto alternativo de la imagen')
                            ->required(
                                fn (Get $get): bool =>
                                    filled($get('og_image'))
                            ),
                    ]),

                Section::make('Indexación')
                    ->description(
                        'Controla las directivas para ' .
                        'los motores de búsqueda.'
                    )
                    ->schema([
                        Toggle::make(
                            "seo_{$lang}.robots_index"
                        )
                            ->label('Permitir indexación')
                            ->default(true)
                            ->helperText(
                                'Activado: index. ' .
                                'Desactivado: noindex.'
                            ),

                        Toggle::make(
                            "seo_{$lang}.robots_follow"
                        )
                            ->label('Permitir seguimiento de enlaces')
                            ->default(true)
                            ->helperText(
                                'Activado: follow. ' .
                                'Desactivado: nofollow.'
                            ),
                    ])
                    ->columns(2),
            ]);
    }

    // ------------------------------------
    // OBTENER VALORES INICIALES
    // ------------------------------------

    private function sectionDefaults(
        array $site,
        string $section,
        string $lang
    ): array {
        $p = $site['perf'];

        return match ($section) {
            'hero' => [
                'crumbServices' => $p['crumbServices'] ?? '',
                'crumbCurrent' => $p['crumbCurrent'] ?? '',
                'kicker' => $p['kicker'] ?? '',
                'h1' => $p['h1'] ?? '',
                'answerTag' => $p['answerTag'] ?? '',
                'answer' => $p['answer'] ?? '',
            ],

            'problem' => [
                'problemH' => $p['problemH'] ?? '',
                'problem' => $p['problem'] ?? '',
            ],

            'capabilities' => [
                'capsH' => $p['capsH'] ?? '',
                'caps' => array_values($p['caps'] ?? []),
            ],

            'process' => [
                'processH' => $p['processH'] ?? '',
                'process' => array_values($p['process'] ?? []),
            ],

            'cases' => [
                'casesH' => $p['casesH'] ?? '',
                'demoTag' => $site['demoTag'] ?? '',
                'cards' => array_values($p['cases'] ?? []),
            ],

            'faq' => [
                'faqH' => $p['faqH'] ?? '',
                'faq' => array_values($p['faq'] ?? []),
            ],

            'cta' => [
                'ctaTitle' => $p['ctaTitle'] ?? '',
                'ctaBody' => $p['ctaBody'] ?? '',
                'ctaBtn' => $p['ctaBtn'] ?? '',
                'ctaHref' => route(
                    'contact',
                    [],
                    false
                ),
            ],

            'related' => [
                'cards' => $this->relatedDefaults(
                    $p['related'] ?? []
                ),
            ],

            'seo' => [
                'meta_title' =>
                    'Performance & Connections — Feat.',

                'meta_description' => $p['answer'] ?? '',
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

    // ------------------------------------
    // TARJETAS RELACIONADAS
    // ------------------------------------

    private function relatedDefaults(array $cards): array
    {
        $result = [];

        foreach ($cards as $card) {
            $routeName = (
                ($card['title'] ?? '') ===
                'Experience & Tech'
            )
                ? 'experience'
                : 'social';

            $result[] = [
                'tag' => $card['tag'] ?? '',
                'title' => $card['title'] ?? '',
                'desc' => $card['desc'] ?? '',
                'cta' => $card['cta'] ?? '',
                'href' => route(
                    $routeName,
                    [],
                    false
                ),

                // Se conserva el valor original del diseño.
                'bg' => $card['bg'] ?? '#FFFFFF',
            ];
        }

        return $result;
    }

    // ------------------------------------
    // GUARDAR TODAS LAS SECCIONES
    // ------------------------------------

    public function save(): void
    {
        $state = $this->form->getState();

        $site = File::json(resource_path('data/site.json'));

        DB::transaction(function () use ($state, $site) {
            foreach (self::SECTIONS as $section) {
                $record = PageSection::firstOrNew([
                    'page' => 'performance',
                    'section' => $section,
                ]);

                foreach (['es', 'en'] as $lang) {
                    $field = 'content_' . $lang;

                    $current = $record->{$field} ?? [];

                    $submitted = $state[
                        $section . '_' . $lang
                    ] ?? [];

                    $content = array_replace(
                        $current,
                        $submitted
                    );

                    // La tarjeta relacionada mantiene siempre
                    // el color original, no un color del CMS.
                    if ($section === 'related') {
                        $originalCards =
                            $site[$lang]['perf']['related']
                            ?? [];

                        $cards = array_values(
                            $content['cards'] ?? []
                        );

                        foreach ($cards as $index => &$card) {
                            $card['bg'] = $originalCards[
                                $index
                            ]['bg'] ?? '#FFFFFF';
                        }

                        unset($card);

                        $content['cards'] = $cards;
                    }

                    $record->{$field} = $content;
                }

                $record->save();
            }
        });

        Notification::make()
            ->title(
                'Contenido de Performance & Connections actualizado correctamente'
            )
            ->success()
            ->send();
    }
}
