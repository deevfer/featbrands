<?php

namespace App\Filament\Pages;

use App\Models\PageSection;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TagsInput;
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

class ManageHomeHero extends Page
{
    protected string $view = 'filament.pages.manage-home-hero';

    protected static ?string $navigationLabel = 'Home';

    protected static ?string $title = 'Administrar Home';

    public ?array $data = [];

    // ------------------------------------
    // CARGAR DATOS DEL FORMULARIO
    // ------------------------------------

    public function mount(): void
    {
        $hero = $this->getHero();
        $proof = $this->getProof();

        $this->form->fill([
            // Sección 1
            'hero_es' => $this->heroData($hero, 'es'),
            'hero_en' => $this->heroData($hero, 'en'),
            'cards_es' => $this->cardsData('es'),
            'cards_en' => $this->cardsData('en'),

            // Sección 2
            'proof_es' => $proof->content_es ?? [],
            'proof_en' => $proof->content_en ?? [],

            // Sección 3
            'services_es' => $this->servicesData('es'),
            'services_en' => $this->servicesData('en'),

            // Sección 4
            'cases_es' => $this->casesData('es'),
            'cases_en' => $this->casesData('en'),
            'cases_media' => $this->casesMediaData(),

            // Sección 5
            'collab_es' => $this->collabData('es'),
            'collab_en' => $this->collabData('en'),
            'collab_clients' => $this->collabClientsData(),

            // Sección 6
            'markets_es' => $this->marketsData('es'),
            'markets_en' => $this->marketsData('en'),
            'market_offices' => $this->marketOfficesData(),

            // Sección 7
            'seo_es' => $this->seoData('es'),
            'seo_en' => $this->seoData('en'),
        ]);
    }

    // ------------------------------------
    // FORMULARIO GENERAL
    // ------------------------------------

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Form::make([
                    Tabs::make('Secciones de Home')
                        ->tabs([
                            Tab::make('1. Hero y carrusel')
                                ->schema([
                                    Tabs::make('Idiomas Hero')
                                        ->tabs([
                                            $this->heroTab('Español', 'es'),
                                            $this->heroTab('Inglés', 'en'),
                                        ]),
                                ]),

                            Tab::make('2. Reconocimientos')
                                ->schema([
                                    Tabs::make('Idiomas Reconocimientos')
                                        ->tabs([
                                            $this->proofTab('Español', 'es'),
                                            $this->proofTab('Inglés', 'en'),
                                        ]),
                                ]),

                            Tab::make('3. Especializaciones')
                                ->schema([
                                    Tabs::make('Idiomas Especializaciones')
                                        ->tabs([
                                            $this->servicesTab('Español', 'es'),
                                            $this->servicesTab('Inglés', 'en'),
                                        ]),
                                ]),

                            Tab::make('4. Resultados y casos')
                                ->schema([
                                    Tabs::make('Idiomas Resultados')
                                        ->tabs([
                                            $this->casesTab('Español', 'es'),
                                            $this->casesTab('Inglés', 'en'),
                                        ]),

                                    Section::make('Material multimedia')
                                        ->description(
                                            'Administra el video y la imagen de portada. ' .
                                            'Estos archivos se comparten entre ambos idiomas.'
                                        )
                                        ->schema([
                                            FileUpload::make('cases_media.video_file')
                                                ->label('Archivo de video')
                                                ->acceptedFileTypes([
                                                    'video/mp4',
                                                    'video/webm',
                                                ])
                                                ->disk('public')
                                                ->directory('home/cases/videos')
                                                ->visibility('public')
                                                ->maxSize(10240)
                                                ->helperText(
                                                    'Selecciona un video MP4 o WebM. ' .
                                                    'Tamaño máximo: 10 MB.'
                                                ),

                                            TextInput::make('cases_media.video_url')
                                                ->label('URL de video externo')
                                                ->url()
                                                ->helperText(
                                                    'Opcional. Permite utilizar ' .
                                                    'un video alojado externamente.'
                                                ),

                                            FileUpload::make('cases_media.poster')
                                                ->label('Imagen de portada')
                                                ->image()
                                                ->acceptedFileTypes([
                                                    'image/jpeg',
                                                    'image/png',
                                                    'image/webp',
                                                ])
                                                ->disk('public')
                                                ->directory('home/cases/posters')
                                                ->visibility('public')
                                                ->maxSize(4096)
                                                ->helperText(
                                                    'Imagen que se mostrará antes de ' .
                                                    'reproducir el video. Máximo 4 MB.'
                                                ),
                                        ]),
                                ]),

                            Tab::make('5. Colaboración y clientes')
                                ->schema([
                                    Tabs::make('Idiomas Colaboración')
                                        ->tabs([
                                            $this->collabTab('Español', 'es'),
                                            $this->collabTab('Inglés', 'en'),
                                        ]),

                                    Section::make('Marcas y logotipos')
                                        ->description(
                                            'Administra las marcas de esta sección. ' .
                                            'Los nombres y logotipos se comparten ' .
                                            'entre ambos idiomas.'
                                        )
                                        ->schema([
                                            Repeater::make('collab_clients')
                                                ->label('Marcas')
                                                ->schema([
                                                    TextInput::make('name')
                                                        ->label('Nombre de la marca')
                                                        ->required(),

                                                    FileUpload::make('logo')
                                                        ->label('Logotipo')
                                                        ->image()
                                                        ->acceptedFileTypes([
                                                            'image/jpeg',
                                                            'image/png',
                                                            'image/webp',
                                                            'image/svg+xml',
                                                        ])
                                                        ->disk('public')
                                                        ->directory('home/clients')
                                                        ->visibility('public')
                                                        ->maxSize(4096)
                                                        ->helperText(
                                                            'JPG, PNG, WebP o SVG. ' .
                                                            'Máximo 4 MB.'
                                                        ),

                                                    TextInput::make('alt_es')
                                                        ->label('Texto alternativo (español)')
                                                        ->required(
                                                            fn (Get $get): bool =>
                                                                filled($get('logo'))
                                                        ),

                                                    TextInput::make('alt_en')
                                                        ->label('Texto alternativo (inglés)')
                                                        ->required(
                                                            fn (Get $get): bool =>
                                                                filled($get('logo'))
                                                        ),
                                                ])
                                                ->itemLabel(
                                                    fn (array $state): ?string =>
                                                        $state['name']
                                                        ?? 'Nueva marca'
                                                )
                                                ->collapsed()
                                                ->addActionLabel('Agregar marca')
                                                ->addable()
                                                ->deletable()
                                                ->reorderable(),
                                        ]),
                                ]),

                            Tab::make('6. Mercados y oficinas')
                                ->schema([
                                    Tabs::make('Idiomas Mercados')
                                        ->tabs([
                                            $this->marketsTab('Español', 'es'),
                                            $this->marketsTab('Inglés', 'en'),
                                        ]),

                                    Section::make('Oficinas')
                                        ->description(
                                            'Administra las oficinas de Guatemala, ' .
                                            'Costa Rica y Miami. Los textos se ' .
                                            'configuran en ambos idiomas.'
                                        )
                                        ->schema([
                                            Repeater::make('market_offices')
                                                ->label('Oficinas disponibles')
                                                ->schema([
                                                    Section::make('Identificación')
                                                        ->description(
                                                            'Los identificadores se mantienen ' .
                                                            'fijos porque se utilizan en ' .
                                                            'la navegación del sitio.'
                                                        )
                                                        ->schema([
                                                            TextInput::make('mk')
                                                                ->label('Identificador interno')
                                                                ->readOnly()
                                                                ->required(),

                                                            TextInput::make('code')
                                                                ->label('Código del mercado')
                                                                ->readOnly()
                                                                ->required(),
                                                        ])
                                                        ->columns(2),

                                                    Section::make('Contenido en español')
                                                        ->schema([
                                                            TextInput::make('city_es')
                                                                ->label('Ciudad')
                                                                ->required(),

                                                            TextInput::make('tag_es')
                                                                ->label('Etiqueta')
                                                                ->required(),

                                                            Textarea::make('message_es')
                                                                ->label('Descripción de la oficina')
                                                                ->rows(3)
                                                                ->required(),

                                                            Textarea::make('address_es')
                                                                ->label('Dirección')
                                                                ->rows(2)
                                                                ->required(),
                                                        ]),

                                                    Section::make('Contenido en inglés')
                                                        ->schema([
                                                            TextInput::make('city_en')
                                                                ->label('Ciudad')
                                                                ->required(),

                                                            TextInput::make('tag_en')
                                                                ->label('Etiqueta')
                                                                ->required(),

                                                            Textarea::make('message_en')
                                                                ->label('Descripción de la oficina')
                                                                ->rows(3)
                                                                ->required(),

                                                            Textarea::make('address_en')
                                                                ->label('Dirección')
                                                                ->rows(2)
                                                                ->required(),
                                                        ]),

                                                    Section::make('Contacto e imagen')
                                                        ->schema([
                                                            TextInput::make('phone')
                                                                ->label('Teléfono')
                                                                ->tel()
                                                                ->required(),

                                                            FileUpload::make('image')
                                                                ->label('Imagen o ilustración')
                                                                ->image()
                                                                ->acceptedFileTypes([
                                                                    'image/jpeg',
                                                                    'image/png',
                                                                    'image/webp',
                                                                    'image/svg+xml',
                                                                ])
                                                                ->disk('public')
                                                                ->directory('home/markets')
                                                                ->visibility('public')
                                                                ->maxSize(4096)
                                                                ->helperText(
                                                                    'Opcional. Si no se carga una imagen, ' .
                                                                    'se conservará la ilustración original.'
                                                                ),

                                                            TextInput::make('image_alt_es')
                                                                ->label('Texto alternativo (español)')
                                                                ->required(
                                                                    fn (Get $get): bool =>
                                                                        filled($get('image'))
                                                                ),

                                                            TextInput::make('image_alt_en')
                                                                ->label('Texto alternativo (inglés)')
                                                                ->required(
                                                                    fn (Get $get): bool =>
                                                                        filled($get('image'))
                                                                ),
                                                        ]),
                                                ])
                                                ->itemLabel(
                                                    fn (array $state): ?string =>
                                                        $state['city_es']
                                                        ?? $state['code']
                                                        ?? 'Oficina'
                                                )
                                                ->collapsed()
                                                ->minItems(3)
                                                ->maxItems(3)
                                                ->addable(false)
                                                ->deletable(false)
                                                ->reorderable(false),
                                        ]),
                                ]),

                            Tab::make('7. SEO')
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
    // SECCIÓN 1 - HERO Y CARRUSEL
    // ------------------------------------

    private function heroTab(string $label, string $lang): Tab
    {
        return Tab::make($label)
            ->schema([
                Section::make('Contenido principal')
                    ->description(
                        'Configura los textos del inicio de Home.'
                    )
                    ->schema([
                        TextInput::make("hero_{$lang}.eyebrow")
                            ->label('Texto superior'),

                        Textarea::make("hero_{$lang}.title")
                            ->label('Título principal')
                            ->helperText(
                                'Escribe el título completo. ' .
                                'El efecto visual se aplicará desde el frontend.'
                            )
                            ->rows(2)
                            ->required(),

                        Textarea::make("hero_{$lang}.sub")
                            ->label('Descripción')
                            ->rows(4)
                            ->required(),
                    ]),

                Section::make('Botones principales')
                    ->description(
                        'Administra los textos y enlaces.'
                    )
                    ->schema([
                        TextInput::make("hero_{$lang}.ctaPrimary")
                            ->label('Texto del botón principal')
                            ->required(),

                        TextInput::make("hero_{$lang}.ctaPrimaryUrl")
                            ->label('Enlace del botón principal')
                            ->helperText('Ejemplo: /contacto')
                            ->required(),

                        TextInput::make("hero_{$lang}.ctaSecondary")
                            ->label('Texto del botón secundario')
                            ->required(),

                        TextInput::make("hero_{$lang}.ctaSecondaryUrl")
                            ->label('Enlace del botón secundario')
                            ->required(),
                    ])
                    ->columns(2),

                Section::make('Tarjetas del carrusel')
                    ->description(
                        'Estas tarjetas también se utilizan ' .
                        'en la sección 3, Especializaciones.'
                    )
                    ->schema([
                        Repeater::make("cards_{$lang}")
                            ->label('Especializaciones')
                            ->schema([
                                TextInput::make('title')
                                    ->label('Título')
                                    ->required(),

                                Textarea::make('desc')
                                    ->label('Descripción')
                                    ->rows(3)
                                    ->required(),

                                TextInput::make('kind')
                                    ->label('Etiqueta adicional'),

                                TagsInput::make('tags')
                                    ->label('Etiquetas'),

                                TextInput::make('cta')
                                    ->label('Texto del botón')
                                    ->required(),

                                TextInput::make('href')
                                    ->label('Enlace del botón')
                                    ->required(),
                            ])
                            ->itemLabel(
                                fn (array $state): ?string =>
                                    $state['title'] ?? 'Tarjeta'
                            )
                            ->collapsed()
                            ->minItems(5)
                            ->maxItems(5)
                            ->addable(false)
                            ->deletable(false)
                            ->reorderable(false),
                    ]),
            ]);
    }

    // ------------------------------------
    // SECCIÓN 2 - RECONOCIMIENTOS
    // ------------------------------------

    private function proofTab(string $label, string $lang): Tab
    {
        return Tab::make($label)
            ->schema([
                Section::make('Encabezado')
                    ->description(
                        'Configura los textos superiores.'
                    )
                    ->schema([
                        TextInput::make("proof_{$lang}.eyebrow")
                            ->label('Texto superior')
                            ->required(),

                        Textarea::make("proof_{$lang}.title")
                            ->label('Título principal')
                            ->rows(2)
                            ->required(),
                    ]),

                Section::make('Tarjetas de reconocimientos')
                    ->description(
                        'Administra imágenes, cifras y descripciones.'
                    )
                    ->schema([
                        Repeater::make("proof_{$lang}.cards")
                            ->label('Tarjetas')
                            ->schema([
                                TextInput::make('big')
                                    ->label('Cifra destacada')
                                    ->required(),

                                Textarea::make('label')
                                    ->label('Descripción de la tarjeta')
                                    ->rows(3)
                                    ->required(),

                                FileUpload::make('image')
                                    ->label('Imagen')
                                    ->image()
                                    ->acceptedFileTypes([
                                        'image/jpeg',
                                        'image/png',
                                        'image/webp',
                                    ])
                                    ->disk('public')
                                    ->directory('home/proof')
                                    ->visibility('public')
                                    ->maxSize(4096)
                                    ->helperText(
                                        'JPG, PNG o WebP. Máximo 4 MB.'
                                    ),

                                TextInput::make('image_alt')
                                    ->label('Texto alternativo de la imagen')
                                    ->helperText(
                                        'Describe la imagen para ' .
                                        'accesibilidad y SEO.'
                                    )
                                    ->required(
                                        fn (Get $get): bool =>
                                            filled($get('image'))
                                    ),
                            ])
                            ->itemLabel(
                                fn (array $state): ?string =>
                                    $state['big'] ?? 'Nueva tarjeta'
                            )
                            ->collapsed()
                            ->addActionLabel('Agregar tarjeta')
                            ->minItems(1)
                            ->addable()
                            ->deletable()
                            ->reorderable(),
                    ]),
            ]);
    }

    // ------------------------------------
    // SECCIÓN 3 - ESPECIALIZACIONES
    // ------------------------------------

    private function servicesTab(string $label, string $lang): Tab
    {
        return Tab::make($label)
            ->schema([
                Section::make('Encabezado de especializaciones')
                    ->description(
                        'Configura los textos superiores.'
                    )
                    ->schema([
                        TextInput::make("services_{$lang}.eyebrow")
                            ->label('Texto superior')
                            ->required(),

                        Textarea::make("services_{$lang}.title")
                            ->label('Título principal')
                            ->helperText(
                                'Escribe el título completo.'
                            )
                            ->rows(2)
                            ->required(),

                        Textarea::make("services_{$lang}.intro")
                            ->label('Descripción')
                            ->rows(4)
                            ->required(),
                    ]),

                Section::make('Botones inferiores')
                    ->description(
                        'Administra los botones debajo de las tarjetas.'
                    )
                    ->schema([
                        TextInput::make("services_{$lang}.ctaPrimaryText")
                            ->label('Texto del botón principal')
                            ->required(),

                        TextInput::make("services_{$lang}.ctaPrimaryUrl")
                            ->label('Enlace del botón principal')
                            ->helperText('Ejemplo: /contacto')
                            ->required(),

                        TextInput::make("services_{$lang}.ctaSecondaryText")
                            ->label('Texto del botón secundario')
                            ->required(),

                        TextInput::make("services_{$lang}.ctaSecondaryUrl")
                            ->label('Enlace del botón secundario')
                            ->required(),
                    ])
                    ->columns(2),

                Section::make('Tarjetas compartidas')
                    ->description(
                        'Las cinco tarjetas se administran desde ' .
                        'la sección 1, Hero y carrusel. ' .
                        'Sus textos y enlaces son compartidos.'
                    )
                    ->schema([]),
            ]);
    }

    // ------------------------------------
    // SECCIÓN 4 - RESULTADOS Y CASOS
    // ------------------------------------

    private function casesTab(string $label, string $lang): Tab
    {
        return Tab::make($label)
            ->schema([
                Section::make('Encabezado de resultados')
                    ->description(
                        'Configura los textos de esta sección.'
                    )
                    ->schema([
                        TextInput::make("cases_{$lang}.eyebrow")
                            ->label('Texto superior')
                            ->required(),

                        Textarea::make("cases_{$lang}.title")
                            ->label('Título principal')
                            ->rows(2)
                            ->required(),
                    ]),

                Section::make('Enlace a casos')
                    ->description(
                        'Configura el enlace a la página de casos.'
                    )
                    ->schema([
                        TextInput::make("cases_{$lang}.exploreText")
                            ->label('Texto del enlace')
                            ->required(),

                        TextInput::make("cases_{$lang}.exploreUrl")
                            ->label('URL del enlace')
                            ->helperText('Ejemplo: /casos')
                            ->required(),
                    ])
                    ->columns(2),

                Section::make('Información del video')
                    ->description(
                        'Administra los textos relacionados con el video.'
                    )
                    ->schema([
                        TextInput::make("cases_{$lang}.videoNote")
                            ->label('Texto descriptivo del video'),

                        TextInput::make("cases_{$lang}.videoLabel")
                            ->label('Descripción del botón de reproducción')
                            ->helperText(
                                'Texto accesible que identifica ' .
                                'la acción de reproducir el video.'
                            ),

                        TextInput::make("cases_{$lang}.posterAlt")
                            ->label('Texto alternativo de la portada'),
                    ]),

                Section::make('Tarjetas de resultados')
                    ->description(
                        'Administra clientes, métricas y descripciones.'
                    )
                    ->schema([
                        Repeater::make("cases_{$lang}.cards")
                            ->label('Casos destacados')
                            ->schema([
                                TextInput::make('client')
                                    ->label('Cliente')
                                    ->required(),

                                TextInput::make('metric')
                                    ->label('Métrica destacada')
                                    ->helperText(
                                        'Ejemplo: +38%, 2.4× o −27%.'
                                    )
                                    ->required(),

                                TextInput::make('metricLabel')
                                    ->label('Descripción de la métrica'),

                                Textarea::make('desc')
                                    ->label('Descripción del caso')
                                    ->rows(3),

                                TextInput::make('href')
                                    ->label('Enlace del caso')
                                    ->helperText(
                                        'Opcional. URL de la página del caso.'
                                    ),
                            ])
                            ->itemLabel(
                                fn (array $state): ?string =>
                                    $state['client'] ?? 'Nuevo caso'
                            )
                            ->collapsed()
                            ->addActionLabel('Agregar caso')
                            ->minItems(1)
                            ->addable()
                            ->deletable()
                            ->reorderable(),
                    ]),
            ]);
    }

    // ------------------------------------
    // SECCIÓN 5 - COLABORACIÓN Y CLIENTES
    // ------------------------------------

    private function collabTab(string $label, string $lang): Tab
    {
        return Tab::make($label)
            ->schema([
                Section::make('Contenido de colaboración')
                    ->description(
                        'Configura la frase destacada y la descripción.'
                    )
                    ->schema([
                        TextInput::make("collab_{$lang}.eyebrow")
                            ->label('Texto superior')
                            ->required(),

                        Textarea::make("collab_{$lang}.quote")
                            ->label('Frase destacada')
                            ->helperText(
                                'Escribe la frase completa. ' .
                                'El diseño aplicará el estilo visual.'
                            )
                            ->rows(3)
                            ->required(),

                        Textarea::make("collab_{$lang}.body")
                            ->label('Descripción')
                            ->rows(4)
                            ->required(),

                        Textarea::make("collab_{$lang}.result")
                            ->label('Frase complementaria')
                            ->rows(2),
                    ]),

                Section::make('Enlace a Nosotros')
                    ->description(
                        'Administra el enlace debajo del texto.'
                    )
                    ->schema([
                        TextInput::make("collab_{$lang}.ctaText")
                            ->label('Texto del enlace')
                            ->required(),

                        TextInput::make("collab_{$lang}.ctaUrl")
                            ->label('URL del enlace')
                            ->helperText('Ejemplo: /nosotros')
                            ->required(),
                    ])
                    ->columns(2),

                Section::make('Encabezado de clientes')
                    ->description(
                        'Configura los textos junto a las marcas.'
                    )
                    ->schema([
                        TextInput::make("collab_{$lang}.clientsTitle")
                            ->label('Título de marcas')
                            ->required(),

                        Textarea::make("collab_{$lang}.clientsNote")
                            ->label('Nota inferior')
                            ->rows(2)
                            ->required(),
                    ]),
            ]);
    }

    // ------------------------------------
    // SECCIÓN 6 - MERCADOS Y OFICINAS
    // ------------------------------------

    private function marketsTab(string $label, string $lang): Tab
    {
        return Tab::make($label)
            ->schema([
                Section::make('Encabezado de mercados')
                    ->description(
                        'Configura los textos junto a las oficinas.'
                    )
                    ->schema([
                        TextInput::make("markets_{$lang}.eyebrow")
                            ->label('Texto superior')
                            ->required(),

                        Textarea::make("markets_{$lang}.title")
                            ->label('Título principal')
                            ->helperText(
                                'Escribe el título completo.'
                            )
                            ->rows(2)
                            ->required(),

                        Textarea::make("markets_{$lang}.body")
                            ->label('Descripción')
                            ->rows(4)
                            ->required(),
                    ]),
            ]);
    }

    // ------------------------------------
    // SECCIÓN 7 - SEO Y LINKEDIN
    // ------------------------------------

    private function seoTab(string $label, string $lang): Tab
    {
        return Tab::make($label)
            ->schema([
                Section::make('SEO básico')
                    ->description(
                        'Configura la información que los ' .
                        'motores de búsqueda pueden utilizar ' .
                        'para identificar esta página.'
                    )
                    ->schema([
                        TextInput::make("seo_{$lang}.meta_title")
                            ->label('Metatítulo')
                            ->required()
                            ->maxLength(255)
                            ->helperText(
                                'Se recomienda un título claro ' .
                                'y descriptivo, de aproximadamente ' .
                                '50 a 60 caracteres.'
                            ),

                        Textarea::make("seo_{$lang}.meta_description")
                            ->label('Metadescripción')
                            ->required()
                            ->rows(3)
                            ->helperText(
                                'Resumen de Home. Como referencia, ' .
                                'se recomiendan aproximadamente ' .
                                '140 a 160 caracteres.'
                            ),

                        TextInput::make("seo_{$lang}.canonical_url")
                            ->label('URL canónica')
                            ->url()
                            ->helperText(
                                'Opcional. Introduce una URL absoluta ' .
                                'con https://. Si está vacía, se ' .
                                'generará automáticamente durante ' .
                                'la integración del frontend.'
                            ),
                    ]),

                Section::make('LinkedIn y redes sociales (Open Graph)')
                    ->description(
                        'Configura cómo se mostrará la página ' .
                        'al compartirla en LinkedIn, Facebook ' .
                        'y otras plataformas compatibles.'
                    )
                    ->schema([
                        TextInput::make("seo_{$lang}.og_title")
                            ->label('Título para LinkedIn y redes sociales')
                            ->maxLength(255)
                            ->helperText(
                                'Opcional. Si está vacío, ' .
                                'se utilizará el metatítulo.'
                            ),

                        Textarea::make("seo_{$lang}.og_description")
                            ->label('Descripción para LinkedIn y redes sociales')
                            ->rows(3)
                            ->helperText(
                                'Opcional. Si está vacía, ' .
                                'se utilizará la metadescripción.'
                            ),

                        FileUpload::make("seo_{$lang}.og_image")
                            ->label('Imagen para LinkedIn y redes sociales')
                            ->image()
                            ->acceptedFileTypes([
                                'image/jpeg',
                                'image/png',
                                'image/webp',
                            ])
                            ->disk('public')
                            ->directory("home/seo/{$lang}")
                            ->visibility('public')
                            ->maxSize(4096)
                            ->helperText(
                                'Tamaño recomendado: 1200 × 630 píxeles. ' .
                                'JPG, PNG o WebP. Máximo 4 MB.'
                            ),

                        TextInput::make("seo_{$lang}.og_image_alt")
                            ->label('Texto alternativo de la imagen')
                            ->helperText(
                                'Describe el contenido de la imagen.'
                            )
                            ->required(
                                fn (Get $get): bool =>
                                    filled($get('og_image'))
                            ),
                    ]),

                Section::make('Indexación')
                    ->description(
                        'Configura las directivas para ' .
                        'los robots de los motores de búsqueda.'
                    )
                    ->schema([
                        Toggle::make("seo_{$lang}.robots_index")
                            ->label('Permitir indexación')
                            ->helperText(
                                'Activado: index. Desactivado: noindex.'
                            )
                            ->default(true),

                        Toggle::make("seo_{$lang}.robots_follow")
                            ->label('Permitir seguimiento de enlaces')
                            ->helperText(
                                'Activado: follow. Desactivado: nofollow.'
                            )
                            ->default(true),
                    ])
                    ->columns(2),
            ]);
    }

    // ------------------------------------
    // CONSULTAS
    // ------------------------------------

    private function getHero(): PageSection
    {
        return PageSection::query()
            ->where('page', 'home')
            ->where('section', 'hero')
            ->firstOrFail();
    }

    private function getProof(): PageSection
    {
        return PageSection::query()
            ->where('page', 'home')
            ->where('section', 'proof')
            ->firstOrFail();
    }

    // ------------------------------------
    // DATOS INICIALES - HERO
    // ------------------------------------

    private function heroData(PageSection $hero, string $lang): array
    {
        $content = $hero->{'content_' . $lang} ?? [];

        $content['title'] ??= trim(
            ($content['titlePre'] ?? '') .
            ($content['titleAccent'] ?? '')
        );

        $content['ctaPrimaryUrl'] ??= '/contacto';
        $content['ctaSecondaryUrl'] ??= '/performance-connections';

        return $content;
    }

    // ------------------------------------
    // DATOS INICIALES - CARRUSEL
    // ------------------------------------

    private function cardsData(string $lang): array
    {
        $section = PageSection::query()
            ->where('page', 'home')
            ->where('section', 'services')
            ->first();

        $saved = $section?->getAttribute('content_' . $lang) ?? [];

        if (!empty($saved['cards'])) {
            return $saved['cards'];
        }

        $site = File::json(resource_path('data/site.json'))[$lang];

        $routes = [
            'performance',
            'brand',
            'experience',
            'makitha',
            'social',
        ];

        $cards = [];

        foreach ($site['services'] as $index => $service) {
            $cards[] = [
                ...$service,
                'cta' => $site['servicesBtn'],
                'href' => route($routes[$index], [], false),
            ];
        }

        return $cards;
    }

    // ------------------------------------
    // DATOS INICIALES - ESPECIALIZACIONES
    // ------------------------------------

    private function servicesData(string $lang): array
    {
        $site = File::json(resource_path('data/site.json'))[$lang];

        $section = PageSection::query()
            ->where('page', 'home')
            ->where('section', 'services')
            ->first();

        $saved = $section?->getAttribute('content_' . $lang) ?? [];

        $defaults = [
            'eyebrow' => $site['servicesKicker'],

            'title' => trim(
                $site['servicesTitle1'] . ' ' .
                $site['servicesTitle2']
            ),

            'intro' => $site['servicesIntro'],

            'ctaPrimaryText' => $site['hero']['card2Btn'],
            'ctaPrimaryUrl' => '/contacto',

            'ctaSecondaryText' => $site['cta'],
            'ctaSecondaryUrl' => '/contacto',
        ];

        return array_replace(
            $defaults,
            array_intersect_key($saved, $defaults)
        );
    }

    // ------------------------------------
    // DATOS INICIALES - CASOS
    // ------------------------------------

    private function casesData(string $lang): array
    {
        $site = File::json(resource_path('data/site.json'))[$lang];

        $section = PageSection::query()
            ->where('page', 'home')
            ->where('section', 'cases')
            ->first();

        $saved = $section?->getAttribute('content_' . $lang) ?? [];

        $defaults = [
            'eyebrow' => $site['casesKicker'],
            'title' => $site['casesTitle'],

            'exploreText' => $lang === 'es'
                ? 'Explorar casos'
                : 'Explore work',

            'exploreUrl' => '/casos',

            'videoNote' => $site['hero']['videoNote'],

            'videoLabel' => $lang === 'es'
                ? 'Reproducir video'
                : 'Play video',

            'posterAlt' => '',

            'cards' => array_map(
                fn (array $card): array => [
                    ...$card,
                    'metricLabel' => $card['metricLabel']
                        ?? $card['label']
                        ?? '',
                    'desc' => $card['desc']
                        ?? $card['description']
                        ?? '',
                    'href' => $card['href'] ?? '',
                ],
                $site['cases']
            ),
        ];

        return array_replace(
            $defaults,
            array_intersect_key($saved, $defaults)
        );
    }

    private function casesMediaData(): array
    {
        $section = PageSection::query()
            ->where('page', 'home')
            ->where('section', 'cases')
            ->first();

        $saved = $section?->content_es ?? [];

        return array_replace(
            [
                'video_file' => null,
                'video_url' => '',
                'poster' => null,
            ],
            $saved['media'] ?? []
        );
    }

    // ------------------------------------
    // DATOS INICIALES - COLABORACIÓN
    // ------------------------------------

    private function collabData(string $lang): array
    {
        $site = File::json(resource_path('data/site.json'))[$lang];

        $section = PageSection::query()
            ->where('page', 'home')
            ->where('section', 'collaboration')
            ->first();

        $saved = $section?->getAttribute('content_' . $lang) ?? [];

        $defaults = [
            'eyebrow' => $site['collabKicker'],
            'quote' => $site['collabQuote'],
            'body' => $site['collabBody'],
            'result' => $site['collabResult'] ?? '',

            'ctaText' => $lang === 'es'
                ? 'Conoce a Feat.'
                : 'Meet Feat.',

            'ctaUrl' => '/nosotros',

            'clientsTitle' => $site['clientsTitle'],
            'clientsNote' => $site['clientsNote'],
        ];

        return array_replace(
            $defaults,
            array_intersect_key($saved, $defaults)
        );
    }

    private function collabClientsData(): array
    {
        $section = PageSection::query()
            ->where('page', 'home')
            ->where('section', 'collaboration')
            ->first();

        $saved = $section?->content_es ?? [];

        if (array_key_exists('clients', $saved)) {
            return array_map(
                function ($client): array {
                    if (is_string($client)) {
                        return [
                            'name' => $client,
                            'logo' => null,
                            'alt_es' => 'Logotipo de ' . $client,
                            'alt_en' => $client . ' logo',
                        ];
                    }

                    return array_replace(
                        [
                            'name' => '',
                            'logo' => null,
                            'alt_es' => '',
                            'alt_en' => '',
                        ],
                        $client
                    );
                },
                $saved['clients'] ?? []
            );
        }

        $site = File::json(resource_path('data/site.json'));

        $clients = [];

        foreach ($site['es']['clients'] as $client) {
            $clients[] = [
                'name' => $client,
                'logo' => null,
                'alt_es' => 'Logotipo de ' . $client,
                'alt_en' => $client . ' logo',
            ];
        }

        return $clients;
    }

    // ------------------------------------
    // DATOS INICIALES - MERCADOS
    // ------------------------------------

    private function marketsData(string $lang): array
    {
        $site = File::json(resource_path('data/site.json'))[$lang];

        $section = PageSection::query()
            ->where('page', 'home')
            ->where('section', 'markets')
            ->first();

        $saved = $section?->getAttribute('content_' . $lang) ?? [];

        $defaults = [
            'eyebrow' => $site['marketKicker'],
            'title' => $site['marketTitle'],
            'body' => $site['marketBody'],
        ];

        return array_replace(
            $defaults,
            array_intersect_key($saved, $defaults)
        );
    }

    private function marketOfficesData(): array
    {
        $section = PageSection::query()
            ->where('page', 'home')
            ->where('section', 'markets')
            ->first();

        $saved = $section?->content_es ?? [];

        if (array_key_exists('offices', $saved)) {
            return $saved['offices'] ?? [];
        }

        $site = File::json(resource_path('data/site.json'));

        $officesEn = [];

        foreach ($site['en']['offices'] as $office) {
            $officesEn[$office['mk']] = $office;
        }

        $offices = [];

        foreach ($site['es']['offices'] as $officeEs) {
            $officeEn = $officesEn[$officeEs['mk']] ?? $officeEs;

            $offices[] = [
                'mk' => $officeEs['mk'],
                'code' => $officeEs['code'],

                'city_es' => $officeEs['city'],
                'tag_es' => $officeEs['tag'],
                'message_es' => $officeEs['message'],
                'address_es' => $officeEs['address'],

                'city_en' => $officeEn['city'],
                'tag_en' => $officeEn['tag'],
                'message_en' => $officeEn['message'],
                'address_en' => $officeEn['address'],

                'phone' => $officeEs['phone'],
                'image' => null,

                'image_alt_es' => '',
                'image_alt_en' => '',
            ];
        }

        return $offices;
    }

    // ------------------------------------
    // DATOS INICIALES - SEO
    // ------------------------------------

    private function seoData(string $lang): array
    {
        $site = File::json(resource_path('data/site.json'))[$lang];

        $section = PageSection::query()
            ->where('page', 'home')
            ->where('section', 'seo')
            ->first();

        $saved = $section?->getAttribute('content_' . $lang) ?? [];

        $defaults = [
            // SEO básico
            'meta_title' => 'Feat. — Collab Driven Growth',
            'meta_description' => $site['hero']['sub'],
            'canonical_url' => '',

            // LinkedIn y redes sociales (Open Graph)
            'og_title' => '',
            'og_description' => '',
            'og_image' => null,
            'og_image_alt' => '',

            // Indexación
            'robots_index' => true,
            'robots_follow' => true,
        ];

        return array_replace(
            $defaults,
            array_intersect_key($saved, $defaults)
        );
    }

    // ------------------------------------
    // GUARDAR TODAS LAS SECCIONES
    // ------------------------------------

    public function save(): void
    {
        $state = $this->form->getState();

        DB::transaction(function () use ($state) {

            // ------------------------------------
            // SECCIÓN 1 - HERO
            // ------------------------------------

            $hero = $this->getHero();

            foreach (['es', 'en'] as $lang) {
                $field = 'content_' . $lang;

                $hero->{$field} = array_replace(
                    $hero->{$field} ?? [],
                    $state['hero_' . $lang] ?? []
                );
            }

            $hero->save();

            // ------------------------------------
            // SECCIONES 1 Y 3 - ESPECIALIZACIONES
            // ------------------------------------

            $services = PageSection::firstOrNew([
                'page' => 'home',
                'section' => 'services',
            ]);

            $editableFields = array_flip([
                'eyebrow',
                'title',
                'intro',
                'ctaPrimaryText',
                'ctaPrimaryUrl',
                'ctaSecondaryText',
                'ctaSecondaryUrl',
            ]);

            foreach (['es', 'en'] as $lang) {
                $field = 'content_' . $lang;

                $content = $services->{$field} ?? [];
                $sectionData = $state['services_' . $lang] ?? [];

                $content = array_replace(
                    $content,
                    array_intersect_key(
                        $sectionData,
                        $editableFields
                    )
                );

                $content['cards'] = array_values(
                    $state['cards_' . $lang] ?? []
                );

                $services->{$field} = $content;
            }

            $services->save();

            // ------------------------------------
            // SECCIÓN 2 - RECONOCIMIENTOS
            // ------------------------------------

            $proof = $this->getProof();

            foreach (['es', 'en'] as $lang) {
                $field = 'content_' . $lang;

                $proof->{$field} = array_replace(
                    $proof->{$field} ?? [],
                    $state['proof_' . $lang] ?? []
                );
            }

            $proof->save();

            // ------------------------------------
            // SECCIÓN 4 - CASOS
            // ------------------------------------

            $cases = PageSection::firstOrNew([
                'page' => 'home',
                'section' => 'cases',
            ]);

            $media = $state['cases_media'] ?? [];

            foreach (['es', 'en'] as $lang) {
                $field = 'content_' . $lang;

                $content = $cases->{$field} ?? [];

                $content = array_replace(
                    $content,
                    $state['cases_' . $lang] ?? []
                );

                $content['media'] = $media;

                $cases->{$field} = $content;
            }

            $cases->save();

            // ------------------------------------
            // SECCIÓN 5 - COLABORACIÓN
            // ------------------------------------

            $collaboration = PageSection::firstOrNew([
                'page' => 'home',
                'section' => 'collaboration',
            ]);

            $clients = array_values(
                $state['collab_clients'] ?? []
            );

            foreach (['es', 'en'] as $lang) {
                $field = 'content_' . $lang;

                $content = $collaboration->{$field} ?? [];

                $content = array_replace(
                    $content,
                    $state['collab_' . $lang] ?? []
                );

                $content['clients'] = $clients;

                $collaboration->{$field} = $content;
            }

            $collaboration->save();

            // ------------------------------------
            // SECCIÓN 6 - MERCADOS
            // ------------------------------------

            $markets = PageSection::firstOrNew([
                'page' => 'home',
                'section' => 'markets',
            ]);

            $offices = array_values(
                $state['market_offices'] ?? []
            );

            foreach (['es', 'en'] as $lang) {
                $field = 'content_' . $lang;

                $content = $markets->{$field} ?? [];

                $content = array_replace(
                    $content,
                    $state['markets_' . $lang] ?? []
                );

                $content['offices'] = $offices;

                $markets->{$field} = $content;
            }

            $markets->save();

            // ------------------------------------
            // SECCIÓN 7 - SEO Y LINKEDIN
            // ------------------------------------

            $seo = PageSection::firstOrNew([
                'page' => 'home',
                'section' => 'seo',
            ]);

            foreach (['es', 'en'] as $lang) {
                $field = 'content_' . $lang;

                $content = $seo->{$field} ?? [];

                // Retirar los campos antiguos de Twitter.
                unset(
                    $content['twitter_card'],
                    $content['twitter_title'],
                    $content['twitter_description']
                );

                // Guardar SEO y Open Graph para LinkedIn.
                $content = array_replace(
                    $content,
                    $state['seo_' . $lang] ?? []
                );

                $seo->{$field} = $content;
            }

            $seo->save();
        });

        Notification::make()
            ->title('Contenido de Home actualizado correctamente')
            ->success()
            ->send();
    }
}
