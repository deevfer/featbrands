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

class ManageBrand extends Page
{
    protected string $view = 'filament.pages.manage-brand';

    protected static ?string $navigationLabel = 'Brand & Consulting';

    protected static ?string $title = 'Administrar Brand & Consulting';

    public ?array $data = [];

    private const SECTIONS = [
        'hero' => '1. Portada',
        'specimen' => '2. Pieza editorial',
        'questions' => '3. Preguntas',
        'system' => '4. Sistema de marca',
        'workshop' => '5. Workshop',
        'closing' => '6. Cierre',
        'seo' => '7. SEO',
    ];

    // ------------------------------------
    // CARGAR CONTENIDO ORIGINAL Y GUARDADO
    // ------------------------------------

    public function mount(): void
    {
        $source = File::json(
            resource_path('data/specializations.json')
        );

        $records = PageSection::query()
            ->where('page', 'brand')
            ->get()
            ->keyBy('section');

        $state = [];

        foreach (['es', 'en'] as $lang) {
            $copy = $source[$lang]['brand'] ?? [];

            foreach (array_keys(self::SECTIONS) as $section) {
                $defaults = $this->sectionDefaults(
                    $copy,
                    $section,
                    $lang
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
                            $this->languageTab($section, 'es'),
                            $this->languageTab($section, 'en'),
                        ]),
                ]);
        }

        return $schema
            ->components([
                Form::make([
                    Tabs::make('Secciones de Brand & Consulting')
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

    private function languageTab(
        string $section,
        string $lang
    ): Tab {
        return Tab::make(
            $lang === 'es' ? 'Español' : 'Inglés'
        )->schema(
            $this->sectionSchema($section, $lang)
        );
    }

    // ------------------------------------
    // CAMPOS DE TODAS LAS SECCIONES
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
                        'Administra los textos de presentación ' .
                        'de Brand & Consulting.'
                    )
                    ->schema([
                        TextInput::make("{$s}.name")
                            ->label('Nombre de la especialización')
                            ->required(),

                        TextInput::make("{$s}.title")
                            ->label('Título principal')
                            ->required(),

                        TextInput::make("{$s}.accent")
                            ->label('Texto destacado del título')
                            ->helperText(
                                'Se mostrará con la tipografía y ' .
                                'el estilo original del elemento em.'
                            )
                            ->required(),

                        Textarea::make("{$s}.intro")
                            ->label('Descripción introductoria')
                            ->rows(5)
                            ->required(),
                    ]),

                Section::make('Textos inferiores de portada')
                    ->schema([
                        TextInput::make("{$s}.kicker")
                            ->label('Texto inferior izquierdo')
                            ->required(),

                        TextInput::make("{$s}.lead")
                            ->label('Texto inferior derecho')
                            ->required(),
                    ])
                    ->columns(2),
            ],

            // --------------------------------
            // 2. PIEZA EDITORIAL
            // --------------------------------

            'specimen' => [
                Section::make('Contenido de la pieza editorial')
                    ->description(
                        'Administra los textos del elemento ' .
                        'visual situado junto al encabezado. ' .
                        'La composición, tipografía y colores ' .
                        'permanecerán intactos.'
                    )
                    ->schema([
                        TextInput::make("{$s}.sheet")
                            ->label('Descripción accesible de la pieza')
                            ->helperText(
                                'Se utiliza en el atributo aria-label.'
                            )
                            ->required(),

                        Textarea::make("{$s}.specimen")
                            ->label('Texto dentro de la pieza')
                            ->rows(4)
                            ->required(),

                        TextInput::make("{$s}.stamp")
                            ->label('Texto del sello')
                            ->required(),

                        Textarea::make("{$s}.note")
                            ->label('Nota debajo de la pieza')
                            ->rows(3)
                            ->required(),
                    ]),
            ],

            // --------------------------------
            // 3. PREGUNTAS
            // --------------------------------

            'questions' => [
                Section::make('Encabezado de preguntas')
                    ->schema([
                        TextInput::make("{$s}.questionsEyebrow")
                            ->label('Texto superior')
                            ->helperText(
                                'Texto original: Strategy first.'
                            )
                            ->required(),

                        TextInput::make("{$s}.questionsTitle")
                            ->label('Título de la sección')
                            ->required(),

                        Textarea::make("{$s}.questionsIntro")
                            ->label('Introducción')
                            ->rows(4)
                            ->required(),
                    ]),

                Section::make('Preguntas desplegables')
                    ->description(
                        'Cada elemento contiene una pregunta ' .
                        'y su respuesta. Se conservará el ' .
                        'funcionamiento original con details.'
                    )
                    ->schema([
                        Repeater::make("{$s}.questions")
                            ->label('Preguntas')
                            ->schema([
                                TextInput::make('question')
                                    ->label('Pregunta')
                                    ->required(),

                                Textarea::make('answer')
                                    ->label('Respuesta')
                                    ->rows(4)
                                    ->required(),
                            ])
                            ->itemLabel(
                                fn (array $state): ?string =>
                                    $state['question']
                                    ?? 'Nueva pregunta'
                            )
                            ->collapsed()
                            ->minItems(1)
                            ->addActionLabel('Agregar pregunta')
                            ->addable()
                            ->deletable()
                            ->reorderable(),
                    ]),
            ],

            // --------------------------------
            // 4. SISTEMA DE MARCA
            // --------------------------------

            'system' => [
                Section::make('Encabezado del sistema')
                    ->schema([
                        TextInput::make("{$s}.systemKicker")
                            ->label('Texto superior')
                            ->required(),

                        TextInput::make("{$s}.systemTitle")
                            ->label('Título principal')
                            ->required(),

                        TextInput::make("{$s}.systemAccent")
                            ->label('Texto destacado del título')
                            ->required(),
                    ]),

                Section::make('01 — Identidad')
                    ->description(
                        'Contenido de la primera tarjeta.'
                    )
                    ->schema([
                        TextInput::make("{$s}.identityLabel")
                            ->label('Etiqueta de identidad')
                            ->required(),

                        Textarea::make("{$s}.identityBody")
                            ->label('Descripción de identidad')
                            ->rows(4)
                            ->required(),
                    ]),

                Section::make('02 — Voz')
                    ->description(
                        'Contenido de la segunda tarjeta.'
                    )
                    ->schema([
                        TextInput::make("{$s}.voiceLabel")
                            ->label('Etiqueta de voz')
                            ->required(),

                        Textarea::make("{$s}.voiceQuote")
                            ->label('Frase destacada')
                            ->rows(3)
                            ->required(),

                        Textarea::make("{$s}.voiceBody")
                            ->label('Descripción de voz')
                            ->rows(4)
                            ->required(),
                    ]),

                Section::make('03 — Ecosistema')
                    ->description(
                        'Contenido de la tercera tarjeta.'
                    )
                    ->schema([
                        TextInput::make("{$s}.ecosystemLabel")
                            ->label('Etiqueta de ecosistema')
                            ->required(),

                        Textarea::make("{$s}.ecosystemTitle")
                            ->label('Título del ecosistema')
                            ->rows(3)
                            ->helperText(
                                'Se conservarán los saltos de ' .
                                'línea mediante la clase preserve-lines.'
                            )
                            ->required(),

                        Textarea::make("{$s}.ecosystemBody")
                            ->label('Descripción del ecosistema')
                            ->rows(4)
                            ->required(),
                    ]),
            ],

            // --------------------------------
            // 5. WORKSHOP
            // --------------------------------

            'workshop' => [
                Section::make('Encabezado del Workshop')
                    ->schema([
                        TextInput::make("{$s}.workshopKicker")
                            ->label('Texto superior')
                            ->required(),

                        TextInput::make("{$s}.workshopTitle")
                            ->label('Título de la sección')
                            ->required(),

                        Textarea::make("{$s}.workshopBody")
                            ->label('Descripción')
                            ->rows(5)
                            ->required(),
                    ]),

                Section::make('Pasos del Workshop')
                    ->description(
                        'Puedes agregar, eliminar o reordenar ' .
                        'los pasos. La numeración visual ' .
                        'continuará generándose automáticamente.'
                    )
                    ->schema([
                        Repeater::make("{$s}.workshopSteps")
                            ->label('Pasos')
                            ->schema([
                                Textarea::make('text')
                                    ->label('Descripción del paso')
                                    ->rows(3)
                                    ->required(),
                            ])
                            ->itemLabel(
                                fn (array $state): ?string =>
                                    $state['text']
                                    ?? 'Nuevo paso'
                            )
                            ->collapsed()
                            ->minItems(1)
                            ->addActionLabel('Agregar paso')
                            ->addable()
                            ->deletable()
                            ->reorderable(),
                    ]),
            ],

            // --------------------------------
            // 6. CIERRE
            // --------------------------------

            'closing' => [
                Section::make('Mensaje final')
                    ->schema([
                        Textarea::make("{$s}.closing")
                            ->label('Mensaje de cierre')
                            ->rows(5)
                            ->required(),
                    ]),

                Section::make('Botón de contacto')
                    ->schema([
                        TextInput::make("{$s}.closingCta")
                            ->label('Texto del botón')
                            ->required(),

                        TextInput::make("{$s}.closingHref")
                            ->label('Enlace del botón')
                            ->required()
                            ->helperText(
                                'Ejemplo: /contacto. ' .
                                'Al conectar el frontend se ' .
                                'conservarán el idioma y el mercado.'
                            ),
                    ])
                    ->columns(2),

                Section::make('Siguiente especialización')
                    ->schema([
                        TextInput::make("{$s}.next")
                            ->label('Texto introductorio')
                            ->required(),

                        TextInput::make("{$s}.nextName")
                            ->label('Nombre del siguiente servicio')
                            ->required(),

                        TextInput::make("{$s}.nextHref")
                            ->label('Enlace del siguiente servicio')
                            ->required()
                            ->helperText(
                                'Originalmente apunta a Social Media.'
                            ),
                    ]),
            ],

            // --------------------------------
            // 7. SEO Y LINKEDIN
            // --------------------------------

            'seo' => [
                Section::make('SEO básico')
                    ->description(
                        'Metadatos de Brand & Consulting.'
                    )
                    ->schema([
                        TextInput::make("{$s}.meta_title")
                            ->label('Metatítulo')
                            ->required()
                            ->maxLength(255)
                            ->helperText(
                                'Recomendación: aproximadamente ' .
                                '50 a 60 caracteres.'
                            ),

                        Textarea::make("{$s}.meta_description")
                            ->label('Metadescripción')
                            ->rows(4)
                            ->required()
                            ->helperText(
                                'Recomendación: aproximadamente ' .
                                '140 a 160 caracteres.'
                            ),

                        TextInput::make("{$s}.canonical_url")
                            ->label('URL canónica')
                            ->url()
                            ->helperText(
                                'Opcional. Si queda vacía, se ' .
                                'generará desde la ruta de Brand.'
                            ),
                    ]),

                Section::make('LinkedIn y redes sociales')
                    ->description(
                        'Metadatos Open Graph para compartir ' .
                        'la página en redes sociales.'
                    )
                    ->schema([
                        TextInput::make("{$s}.og_title")
                            ->label('Título para redes sociales')
                            ->maxLength(255)
                            ->helperText(
                                'Opcional. Se utilizará el ' .
                                'metatítulo si queda vacío.'
                            ),

                        Textarea::make("{$s}.og_description")
                            ->label('Descripción para redes sociales')
                            ->rows(4)
                            ->helperText(
                                'Opcional. Se utilizará la ' .
                                'metadescripción si queda vacía.'
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
                            ->directory("brand/seo/{$lang}")
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
                            ->default(true)
                            ->helperText(
                                'Activado: index. ' .
                                'Desactivado: noindex.'
                            ),

                        Toggle::make("{$s}.robots_follow")
                            ->label('Permitir seguimiento de enlaces')
                            ->default(true)
                            ->helperText(
                                'Activado: follow. ' .
                                'Desactivado: nofollow.'
                            ),
                    ])
                    ->columns(2),
            ],

            default => [],
        };
    }

    // ------------------------------------
    // VALORES ORIGINALES
    // ------------------------------------

    private function sectionDefaults(
        array $copy,
        string $section,
        string $lang
    ): array {
        return match ($section) {

            'hero' => $this->pick($copy, [
                'name',
                'title',
                'accent',
                'intro',
                'kicker',
                'lead',
            ]),

            'specimen' => $this->pick($copy, [
                'sheet',
                'specimen',
                'stamp',
                'note',
            ]),

            'questions' => [
                'questionsEyebrow' => 'Strategy first.',

                ...$this->pick($copy, [
                    'questionsTitle',
                    'questionsIntro',
                ]),

                'questions' => array_values(
                    array_map(
                        static function ($item): array {
                            return [
                                'question' => is_array($item)
                                    ? (string) ($item[0] ?? '')
                                    : '',

                                'answer' => is_array($item)
                                    ? (string) ($item[1] ?? '')
                                    : '',
                            ];
                        },
                        $copy['questions'] ?? []
                    )
                ),
            ],

            'system' => $this->pick($copy, [
                'systemKicker',
                'systemTitle',
                'systemAccent',
                'identityLabel',
                'identityBody',
                'voiceLabel',
                'voiceQuote',
                'voiceBody',
                'ecosystemLabel',
                'ecosystemTitle',
                'ecosystemBody',
            ]),

            'workshop' => [
                ...$this->pick($copy, [
                    'workshopKicker',
                    'workshopTitle',
                    'workshopBody',
                ]),

                'workshopSteps' => array_values(
                    array_map(
                        static fn ($step): array => [
                            'text' => (string) $step,
                        ],
                        $copy['workshopSteps'] ?? []
                    )
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
                    'social',
                    [],
                    false
                ),
            ],

            'seo' => [
                'meta_title' => 'Brand & Consulting — Feat.',

                'meta_description' => $copy['intro'] ?? '',

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
    // GUARDAR LAS SIETE SECCIONES
    // ------------------------------------

    public function save(): void
    {
        $state = $this->form->getState();

        DB::transaction(function () use ($state) {
            foreach (array_keys(self::SECTIONS) as $section) {
                $record = PageSection::firstOrNew([
                    'page' => 'brand',
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
                'Brand & Consulting actualizado correctamente'
            )
            ->success()
            ->send();
    }
}
