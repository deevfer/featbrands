<?php

namespace App\Http\Controllers;

use App\Models\PageSection;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class SiteController extends Controller
{
    public function home(Request $request): View
    {
        return $this->render($request, 'home');
    }

    public function performance(Request $request): View
    {
        return $this->render($request, 'performance');
    }

    public function brand(Request $request): View
    {
        return $this->render($request, 'brand');
    }

    public function social(Request $request): View
    {
        return $this->render($request, 'social');
    }

    public function experience(Request $request): View
    {
        return $this->render($request, 'experience');
    }

    public function makitha(Request $request): View
    {
        return $this->render($request, 'makitha');
    }

    public function cases(Request $request): View
    {
        return $this->render($request, 'cases');
    }

    public function about(Request $request): View
    {
        return $this->render($request, 'about');
    }

    public function contact(Request $request): View
    {
        return $this->render($request, 'contact');
    }

    private function render(Request $request, string $page): View
    {
        $locale = $request->query('lang') === 'en' ? 'en' : 'es';

        $market = in_array(
            $request->query('market'),
            ['gt', 'cr', 'us'],
            true
        ) ? $request->query('market') : 'gt';

        $query = [
            'lang' => $locale,
            'market' => $market,
        ];

        // Datos originales: se mantienen como respaldo.
        $t = File::json(resource_path('data/site.json'))[$locale];

        $homeUrl = route('home', $query);
        $performanceUrl = route('performance', $query);
        $contactUrl = route('contact', $query);

        $serviceRoutes = [
            'performance',
            'brand',
            'experience',
            'makitha',
            'social',
        ];

        $serviceUrls = array_combine(
            $serviceRoutes,
            array_map(
                fn (string $route): string => route($route, $query),
                $serviceRoutes
            )
        );

        $agencyUrls = [
            'cases' => route('cases', $query),
            'about' => route('about', $query),
            'awards' => route('about', $query) . '#reconocimientos',
        ];

        $copy = match ($page) {
            'home', 'performance' => null,

            'cases', 'about' => File::json(
                resource_path('data/agency.json')
            )[$locale][$page],

            'contact' => File::json(
                resource_path('data/contact.json')
            )[$locale],

            default => File::json(
                resource_path('data/specializations.json')
            )[$locale][$page],
        };

        // Variables adicionales exclusivas de Home.
        $homeCms = [];
        $pageSeo = null;

        // Solo Home consulta el contenido del CMS.
        // Las demás páginas conservan su funcionamiento.
        if ($page === 'home') {
            $sections = PageSection::query()
                ->where('page', 'home')
                ->get()
                ->keyBy('section');

            $read = fn (string $name, string $lang): array =>
                $sections->get($name)?->getAttribute(
                    'content_' . $lang
                ) ?? [];

            $hero = $read('hero', $locale);
            $proof = $read('proof', $locale);
            $services = $read('services', $locale);
            $cases = $read('cases', $locale);
            $collab = $read('collaboration', $locale);
            $marketsData = $read('markets', $locale);
            $seo = $read('seo', $locale);

            // ------------------------------------
            // SECCIÓN 1 - HERO
            // ------------------------------------

            foreach ([
                'eyebrow',
                'sub',
                'ctaPrimary',
                'ctaSecondary',
            ] as $field) {
                if (array_key_exists($field, $hero)) {
                    $t['hero'][$field] = $hero[$field];
                }
            }

            // El CMS permite escribir el título completo.
            // Conservamos el subrayado animado de la última palabra.
            $heroTitle = trim((string) (
                $hero['title']
                ?? (
                    $t['hero']['titlePre']
                    . $t['hero']['titleAccent']
                )
            ));

            if ($heroTitle !== '') {
                if (preg_match(
                    '/^(.*\s)(\S+)$/us',
                    $heroTitle,
                    $parts
                )) {
                    $t['hero']['titlePre'] = $parts[1];
                    $t['hero']['titleAccent'] = $parts[2];
                } else {
                    $t['hero']['titlePre'] = '';
                    $t['hero']['titleAccent'] = $heroTitle;
                }
            }

            $homeCms['hero'] = [
                'primary_url' => $this->cmsUrl(
                    $hero['ctaPrimaryUrl'] ?? null,
                    $contactUrl,
                    $query
                ),
                'secondary_url' => $this->cmsUrl(
                    $hero['ctaSecondaryUrl'] ?? null,
                    $performanceUrl,
                    $query
                ),
            ];

            // ------------------------------------
            // SECCIÓN 2 - RECONOCIMIENTOS
            // ------------------------------------

            $t['proofKicker'] =
                $proof['eyebrow'] ?? $t['proofKicker'];

            $t['proofTitle'] =
                $proof['title'] ?? $t['proofTitle'];

            $proofCards = $proof['cards'] ?? $t['proof'];

            $t['proof'] = [];

            foreach ($proofCards as $index => $card) {
                if (!is_array($card)) {
                    continue;
                }

                $imageUrl = $this->storedImageUrl(
                    $card['image'] ?? null
                );

                $t['proof'][] = [
                    'big' => $card['big'] ?? '',
                    'label' => $card['label'] ?? '',

                    'image_url' => $imageUrl
                        ?: asset(
                            'uploads/proof-bg-' . $index . '.webp'
                        ),

                    'image_alt' => $card['image_alt']
                        ?? $card['label']
                        ?? '',
                ];
            }

            // ------------------------------------
            // SECCIÓN 3 - ESPECIALIZACIONES
            // ------------------------------------

            $t['servicesKicker'] =
                $services['eyebrow'] ?? $t['servicesKicker'];

            $servicesTitle = trim((string) (
                $services['title']
                ?? (
                    $t['servicesTitle1']
                    . ' '
                    . $t['servicesTitle2']
                )
            ));

            // Conservar las dos líneas cuando el título
            // contiene una coma, como el título original.
            if (preg_match(
                '/^(.+[,;:])\s+(.+)$/us',
                $servicesTitle,
                $parts
            )) {
                $t['servicesTitle1'] = $parts[1];
                $t['servicesTitle2'] = $parts[2];
            } else {
                $t['servicesTitle1'] = $servicesTitle;
                $t['servicesTitle2'] = '';
            }

            $t['servicesIntro'] =
                $services['intro'] ?? $t['servicesIntro'];

            // Las cinco tarjetas del Hero también se
            // reutilizan en la sección Especializaciones.
            foreach (
                ($services['cards'] ?? []) as $index => $card
            ) {
                if (
                    !isset($t['services'][$index])
                    || !is_array($card)
                ) {
                    continue;
                }

                $t['services'][$index] = array_replace(
                    $t['services'][$index],
                    $card
                );
            }

            foreach ($t['services'] as $index => $service) {
                $fallbackUrl = $serviceUrls[
                    $serviceRoutes[$index]
                ];

                $t['services'][$index]['href'] =
                    $this->cmsUrl(
                        $service['href'] ?? null,
                        $fallbackUrl,
                        $query
                    );

                $t['services'][$index]['cta'] =
                    $service['cta'] ?? $t['servicesBtn'];
            }

            $homeCms['services'] = [
                'primary_text' =>
                    $services['ctaPrimaryText']
                    ?? $t['hero']['card2Btn'],

                'primary_url' => $this->cmsUrl(
                    $services['ctaPrimaryUrl'] ?? null,
                    $contactUrl,
                    $query
                ),

                'secondary_text' =>
                    $services['ctaSecondaryText']
                    ?? $t['cta'],

                'secondary_url' => $this->cmsUrl(
                    $services['ctaSecondaryUrl'] ?? null,
                    $contactUrl,
                    $query
                ),
            ];

            // ------------------------------------
            // SECCIÓN 4 - RESULTADOS Y CASOS
            // ------------------------------------

            $t['casesKicker'] =
                $cases['eyebrow'] ?? $t['casesKicker'];

            $t['casesTitle'] =
                $cases['title'] ?? $t['casesTitle'];

            $caseCards = $cases['cards'] ?? $t['cases'];

            $t['cases'] = [];

            foreach ($caseCards as $card) {
                if (!is_array($card)) {
                    continue;
                }

                $card['href'] = filled(
                    $card['href'] ?? null
                )
                    ? $this->cmsUrl(
                        $card['href'],
                        '',
                        $query
                    )
                    : '';

                $t['cases'][] = $card;
            }

            $media = $cases['media'] ?? [];

            $videoSource = $this->storedImageUrl(
                $media['video_file'] ?? null
            );

            $videoType = 'video/mp4';

            if (
                $videoSource
                && strtolower(pathinfo(
                    (string) $media['video_file'],
                    PATHINFO_EXTENSION
                )) === 'webm'
            ) {
                $videoType = 'video/webm';
            }

            $embedUrl = null;

            if (!$videoSource) {
                $external = $this->externalVideo(
                    $media['video_url'] ?? null
                );

                $videoSource = $external['src'];
                $embedUrl = $external['embed'];
                $videoType = $external['type'];
            }

            $homeCms['cases'] = [
                'explore_text' => $cases['exploreText']
                    ?? (
                        $locale === 'es'
                            ? 'Explorar casos'
                            : 'Explore work'
                    ),

                'explore_url' => $this->cmsUrl(
                    $cases['exploreUrl'] ?? null,
                    $agencyUrls['cases'],
                    $query
                ),

                'video_note' => $cases['videoNote']
                    ?? $t['hero']['videoNote'],

                'video_label' => $cases['videoLabel']
                    ?? (
                        $locale === 'es'
                            ? 'Reproducir video'
                            : 'Play video'
                    ),

                'poster_alt' => $cases['posterAlt'] ?? '',

                'video_src' => $videoSource,
                'video_type' => $videoType,
                'embed_url' => $embedUrl,

                'poster_url' => $this->storedImageUrl(
                    $media['poster'] ?? null
                ),
            ];

            // ------------------------------------
            // SECCIÓN 5 - COLABORACIÓN
            // ------------------------------------

            $t['collabKicker'] =
                $collab['eyebrow'] ?? $t['collabKicker'];

            $t['collabQuote'] =
                $collab['quote'] ?? $t['collabQuote'];

            $t['collabBody'] =
                $collab['body'] ?? $t['collabBody'];

            $t['collabResult'] =
                $collab['result']
                ?? ($t['collabResult'] ?? '');

            $t['clientsTitle'] =
                $collab['clientsTitle']
                ?? $t['clientsTitle'];

            $t['clientsNote'] =
                $collab['clientsNote']
                ?? $t['clientsNote'];

            $homeCms['collaboration'] = [
                'cta_text' => $collab['ctaText']
                    ?? (
                        $locale === 'es'
                            ? 'Conoce a Feat.'
                            : 'Meet Feat.'
                    ),

                'cta_url' => $this->cmsUrl(
                    $collab['ctaUrl'] ?? null,
                    $agencyUrls['about'],
                    $query
                ),
            ];

            $homeCms['clients'] = [];

            foreach (
                ($collab['clients'] ?? $t['clients'])
                as $client
            ) {
                if (is_string($client)) {
                    $homeCms['clients'][] = [
                        'name' => $client,
                        'logo_url' => null,
                        'alt' => $client,
                    ];

                    continue;
                }

                if (!is_array($client)) {
                    continue;
                }

                $homeCms['clients'][] = [
                    'name' => $client['name'] ?? '',

                    'logo_url' => $this->storedImageUrl(
                        $client['logo'] ?? null
                    ),

                    'alt' => $client[
                        'alt_' . $locale
                    ] ?? $client['name'] ?? '',
                ];
            }

            // ------------------------------------
            // SECCIÓN 6 - MERCADOS
            // ------------------------------------

            $t['marketKicker'] =
                $marketsData['eyebrow']
                ?? $t['marketKicker'];

            $t['marketTitle'] =
                $marketsData['title']
                ?? $t['marketTitle'];

            $t['marketBody'] =
                $marketsData['body']
                ?? $t['marketBody'];

            $savedOffices = $marketsData['offices']
                ?? $read('markets', 'es')['offices']
                ?? [];

            $officesByMarket = [];

            foreach ($savedOffices as $office) {
                if (
                    is_array($office)
                    && isset($office['mk'])
                ) {
                    $officesByMarket[$office['mk']] = $office;
                }
            }

            foreach ($t['offices'] as $index => $office) {
                $code = $office['mk'];

                $stored = $officesByMarket[$code] ?? [];

                $t['offices'][$index] = [
                    'mk' => $code,
                    'code' => $office['code'],

                    'city' => $stored[
                        'city_' . $locale
                    ] ?? $office['city'],

                    'tag' => $stored[
                        'tag_' . $locale
                    ] ?? $office['tag'],

                    'message' => $stored[
                        'message_' . $locale
                    ] ?? $office['message'],

                    'address' => $stored[
                        'address_' . $locale
                    ] ?? $office['address'],

                    'phone' => $stored['phone']
                        ?? $office['phone'],

                    'image_url' => $this->storedImageUrl(
                        $stored['image'] ?? null
                    ) ?: asset('assets/ill-' . $code . '.svg'),

                    'image_alt' => $stored[
                        'image_alt_' . $locale
                    ] ?? $office['city'],
                ];
            }

            // ------------------------------------
            // SECCIÓN 7 - SEO Y LINKEDIN
            // ------------------------------------

            $defaultTitle = 'Feat. — Collab Driven Growth';

            $seoTitle = trim((string) (
                $seo['meta_title'] ?? ''
            ));

            $seoDescription = trim((string) (
                $seo['meta_description'] ?? ''
            ));

            $canonical = trim((string) (
                $seo['canonical_url'] ?? ''
            ));

            if (
                !filter_var($canonical, FILTER_VALIDATE_URL)
                || !in_array(
                    strtolower(
                        (string) parse_url(
                            $canonical,
                            PHP_URL_SCHEME
                        )
                    ),
                    ['http', 'https'],
                    true
                )
            ) {
                // Una URL canónica por idioma.
                // Los parámetros de mercado no generan
                // páginas canónicas duplicadas.
                $canonical = route(
                    'home',
                    ['lang' => $locale]
                );
            }

            $pageSeo = [
                'title' => $seoTitle !== ''
                    ? $seoTitle
                    : $defaultTitle,

                'description' => $seoDescription !== ''
                    ? $seoDescription
                    : $t['hero']['sub'],

                'canonical' => $canonical,

                'og_title' => filled(
                    $seo['og_title'] ?? null
                )
                    ? $seo['og_title']
                    : (
                        $seoTitle !== ''
                            ? $seoTitle
                            : $defaultTitle
                    ),

                'og_description' => filled(
                    $seo['og_description'] ?? null
                )
                    ? $seo['og_description']
                    : (
                        $seoDescription !== ''
                            ? $seoDescription
                            : $t['hero']['sub']
                    ),

                'og_image' => $this->absoluteImageUrl(
                    $seo['og_image'] ?? null
                ),

                'og_image_alt' =>
                    $seo['og_image_alt'] ?? '',

                'robots' => (
                    ($seo['robots_index'] ?? true)
                        ? 'index'
                        : 'noindex'
                ) . ', ' . (
                    ($seo['robots_follow'] ?? true)
                        ? 'follow'
                        : 'nofollow'
                ),

                'og_locale' => $locale === 'es'
                    ? 'es_LA'
                    : 'en_US',

                'alternates' => [
                    'es' => route('home', [
                        'lang' => 'es',
                    ]),
                    'en' => route('home', [
                        'lang' => 'en',
                    ]),
                ],
            ];
        }

        // ------------------------------------
        if ($page === 'performance') {
            [$t, $pageSeo] = $this->performanceCms(
                $t,
                $locale,
                $query,
                $contactUrl,
                $serviceUrls
            );
        }

        // TÍTULOS Y DESCRIPCIONES GENERALES
        // ------------------------------------

        $pageTitle = match ($page) {
            'home' => 'Feat. — Collab Driven Growth',
            'performance' =>
                'Performance & Connections — Feat.',
            default => $copy['name'] . ' — Feat.',
        };

        $pageDescription = match ($page) {
            'home' => $t['hero']['sub'],
            'performance' => $t['perf']['answer'],
            default => $copy['intro'],
        };

        if (in_array($page, ['home', 'performance'], true) && $pageSeo) {
            $pageTitle = $pageSeo['title'];
            $pageDescription = $pageSeo['description'];
        }

        // ------------------------------------
        // TARJETAS Y NAVEGACIÓN
        // ------------------------------------

        $colors = [
            ['#FB6C26', '#000000'],
            ['#2D48F2', '#FFFFFF'],
            ['#15F7A8', '#000000'],
            ['#FDDB02', '#000000'],
            ['#D48BFD', '#000000'],
        ];

        $serviceLinks = [];
        $deckCards = [];

        foreach ($t['services'] as $index => $service) {
            $defaultHref = $serviceUrls[
                $serviceRoutes[$index]
            ];

            $href = $page === 'home'
                ? ($service['href'] ?? $defaultHref)
                : $defaultHref;

            $serviceLinks[] = [
                'title' => $service['title'],
                'href' => $href,
                'route' => $serviceRoutes[$index],
                'color' => [
                    '#2D48F2',
                    '#FB6C26',
                    '#15F7A8',
                    '#FDDB02',
                    '#D48BFD',
                ][$index],
            ];

            $deckCards[] = [
                ...$service,
                'num' => sprintf('%02d', $index + 1),
                'background' => $colors[$index][0],
                'foreground' => $colors[$index][1],
                'cta' => $page === 'home'
                    ? ($service['cta'] ?? $t['servicesBtn'])
                    : $t['servicesBtn'],
                'href' => $href,
            ];
        }

        $navRest = [
            [
                'label' => $t['nav'][1],
                'href' => $agencyUrls['cases'],
                'route' => 'cases',
            ],
            [
                'label' => $t['nav'][2],
                'href' => $agencyUrls['about'],
                'route' => 'about',
            ],
            [
                'label' => $t['nav'][3],
                'href' => $homeUrl,
                'route' => null,
            ],
            [
                'label' => $t['nav'][4],
                'href' => $contactUrl,
                'route' => 'contact',
            ],
        ];

        return view('site.' . $page, [
            't' => $t,
            'p' => $t['perf'],
            'page' => $page,
            'copy' => $copy,

            'pageTitle' => $pageTitle,
            'pageDescription' => $pageDescription,
            'pageSeo' => $pageSeo,
            'homeCms' => $homeCms,

            'serviceUrls' => $serviceUrls,
            'agencyUrls' => $agencyUrls,

            'footerAgencyUrls' => [
                $agencyUrls['about'],
                $agencyUrls['cases'],
                $agencyUrls['awards'],
                $homeUrl,
            ],

            'locale' => $locale,
            'market' => $market,
            'homeUrl' => $homeUrl,
            'contactUrl' => $contactUrl,

            'phoneCountries' => $page === 'contact'
                ? collect(
                    File::json(
                        resource_path(
                            'data/calling-codes.json'
                        )
                    )['countries']
                )
                    ->sortBy(
                        'name.' . $locale,
                        SORT_NATURAL | SORT_FLAG_CASE
                    )
                    ->values()
                    ->all()
                : [],

            'performanceUrl' => $performanceUrl,

            'languageUrls' => [
                'es' => route(
                    $request->route()->getName(),
                    [
                        'lang' => 'es',
                        'market' => $market,
                    ]
                ),
                'en' => route(
                    $request->route()->getName(),
                    [
                        'lang' => 'en',
                        'market' => $market,
                    ]
                ),
            ],

            'serviceLinks' => $serviceLinks,
            'deckCards' => $deckCards,
            'navRest' => $navRest,

            'markets' => [
                ['code' => 'GT', 'name' => 'Guatemala'],
                ['code' => 'CR', 'name' => 'Costa Rica'],
                ['code' => 'US', 'name' => 'Miami, US'],
            ],
        ]);
    }

    // ------------------------------------
    // RESOLVER IMÁGENES DEL CMS
    // ------------------------------------

    private function storedImageUrl(?string $path): ?string
    {
        if (
            blank($path)
            || !Storage::disk('public')->exists($path)
        ) {
            return null;
        }

        return Storage::disk('public')->url($path);
    }

    private function absoluteImageUrl(?string $path): ?string
    {
        $imageUrl = $this->storedImageUrl($path);

        return $imageUrl ? url($imageUrl) : null;
    }

    // ------------------------------------
    // RESOLVER ENLACES EDITABLES
    // ------------------------------------

    private function cmsUrl(
        ?string $value,
        string $fallback,
        array $query
    ): string {
        $value = trim($value ?? '');

        if ($value === '') {
            return $fallback;
        }

        if (str_starts_with($value, '#')) {
            return $value;
        }

        if (preg_match('~^https?://~i', $value)) {
            return filter_var(
                $value,
                FILTER_VALIDATE_URL
            ) ? $value : $fallback;
        }

        if (
            str_starts_with($value, 'mailto:')
            || str_starts_with($value, 'tel:')
        ) {
            return $value;
        }

        // Rechazar esquemas inseguros.
        if (
            str_starts_with($value, '//')
            || preg_match(
                '/^[a-z][a-z0-9+.-]*:/i',
                $value
            )
        ) {
            return $fallback;
        }

        $parts = parse_url($value);

        if ($parts === false) {
            return $fallback;
        }

        $path = $parts['path'] ?? '/';

        parse_str(
            $parts['query'] ?? '',
            $parameters
        );

        // Preservar idioma y mercado al navegar
        // entre páginas internas.
        $parameters = array_merge(
            $parameters,
            $query
        );

        $url = url(ltrim($path, '/'));

        $queryString = http_build_query(
            $parameters,
            '',
            '&',
            PHP_QUERY_RFC3986
        );

        if ($queryString !== '') {
            $url .= '?' . $queryString;
        }

        if (!empty($parts['fragment'])) {
            $url .= '#' . $parts['fragment'];
        }

        return $url;
    }

    // ------------------------------------
    // RESOLVER VIDEOS EXTERNOS
    // ------------------------------------

    private function externalVideo(?string $value): array
    {
        $result = [
            'src' => null,
            'embed' => null,
            'type' => 'video/mp4',
        ];

        if (
            blank($value)
            || !filter_var(
                $value,
                FILTER_VALIDATE_URL
            )
        ) {
            return $result;
        }

        $scheme = strtolower(
            (string) parse_url(
                $value,
                PHP_URL_SCHEME
            )
        );

        if (!in_array(
            $scheme,
            ['http', 'https'],
            true
        )) {
            return $result;
        }

        $host = strtolower(
            (string) parse_url(
                $value,
                PHP_URL_HOST
            )
        );

        $path = (string) parse_url(
            $value,
            PHP_URL_PATH
        );

        // Enlace directo a MP4 o WebM.
        if (preg_match('/\.mp4$/i', $path)) {
            $result['src'] = $value;
            return $result;
        }

        if (preg_match('/\.webm$/i', $path)) {
            $result['src'] = $value;
            $result['type'] = 'video/webm';
            return $result;
        }

        // YouTube
        $youtubeId = null;

        if (in_array($host, [
            'youtu.be',
            'www.youtu.be',
        ], true)) {
            $youtubeId = trim($path, '/');
        }

        if (in_array($host, [
            'youtube.com',
            'www.youtube.com',
            'm.youtube.com',
        ], true)) {
            if (str_starts_with($path, '/watch')) {
                parse_str(
                    (string) parse_url(
                        $value,
                        PHP_URL_QUERY
                    ),
                    $parameters
                );
                $youtubeId = $parameters['v'] ?? null;
            } elseif (
                preg_match(
                    '~^/(?:embed|shorts)/([^/]+)~',
                    $path,
                    $matches
                )
            ) {
                $youtubeId = $matches[1];
            }
        }

        if (
            $youtubeId
            && preg_match(
                '/^[a-zA-Z0-9_-]{11}$/',
                $youtubeId
            )
        ) {
            $result['embed'] =
                'https://www.youtube-nocookie.com/embed/'
                . $youtubeId;

            return $result;
        }

        // Vimeo
        if (in_array($host, [
            'vimeo.com',
            'www.vimeo.com',
            'player.vimeo.com',
        ], true)) {
            if (preg_match(
                '~/(?:video/)?(\d+)(?:/|$)~',
                $path,
                $matches
            )) {
                $result['embed'] =
                    'https://player.vimeo.com/video/'
                    . $matches[1];
            }
        }

        return $result;
    }
    // ------------------------------------
    // CMS - PERFORMANCE & CONNECTIONS
    // ------------------------------------

    private function performanceCms(
        array $t,
        string $locale,
        array $query,
        string $contactUrl,
        array $serviceUrls
    ): array {
        $records = PageSection::query()
            ->where('page', 'performance')
            ->get()
            ->keyBy('section');

        $read = static function (string $section) use (
            $records,
            $locale
        ): array {
            $content = $records
                ->get($section)
                ?->getAttribute('content_' . $locale);

            return is_array($content) ? $content : [];
        };

        // ------------------------------------
        // TEXTOS DE LAS SECCIONES
        // ------------------------------------

        $fieldGroups = [
            'hero' => [
                'crumbServices',
                'crumbCurrent',
                'kicker',
                'h1',
                'answerTag',
                'answer',
            ],
            'problem' => [
                'problemH',
                'problem',
            ],
            'capabilities' => [
                'capsH',
            ],
            'process' => [
                'processH',
            ],
            'cases' => [
                'casesH',
            ],
            'faq' => [
                'faqH',
            ],
            'cta' => [
                'ctaTitle',
                'ctaBody',
                'ctaBtn',
            ],
        ];

        foreach ($fieldGroups as $section => $fields) {
            $saved = $read($section);

            foreach ($fields as $field) {
                if (array_key_exists($field, $saved)) {
                    $t['perf'][$field] = $saved[$field];
                }
            }
        }

        // ------------------------------------
        // LISTADOS DINÁMICOS
        // ------------------------------------

        $lists = [
            'capabilities' => ['saved' => 'caps', 'view' => 'caps'],
            'process' => ['saved' => 'process', 'view' => 'process'],
            'cases' => ['saved' => 'cards', 'view' => 'cases'],
            'faq' => ['saved' => 'faq', 'view' => 'faq'],
        ];

        foreach ($lists as $section => $keys) {
            $saved = $read($section);

            $items = $saved[$keys['saved']] ?? null;

            if (!is_array($items)) {
                continue;
            }

            $t['perf'][$keys['view']] = array_values(
                array_filter(
                    $items,
                    static fn ($item): bool => is_array($item)
                )
            );
        }

        // La etiqueta de los casos solo cambia en Performance.
        $caseSection = $read('cases');

        if (array_key_exists('demoTag', $caseSection)) {
            $t['demoTag'] = $caseSection['demoTag'];
        }

        // ------------------------------------
        // BOTÓN DEL LLAMADO A LA ACCIÓN
        // ------------------------------------

        $ctaSection = $read('cta');

        $t['perf']['ctaHref'] = $this->cmsUrl(
            $ctaSection['ctaHref'] ?? null,
            $contactUrl,
            $query
        );

        // ------------------------------------
        // SERVICIOS RELACIONADOS
        // ------------------------------------

        $relatedSection = $read('related');

        $savedCards = $relatedSection['cards'] ?? [];

        $originalCards = $t['perf']['related'] ?? [];

        $relatedCards = [];

        foreach ($originalCards as $index => $original) {
            $saved = is_array($savedCards[$index] ?? null)
                ? $savedCards[$index]
                : [];

            // Solo se actualizan textos y enlaces.
            // Los colores originales no se modifican.
            $card = array_replace(
                $original,
                array_intersect_key(
                    $saved,
                    array_flip([
                        'tag',
                        'title',
                        'desc',
                        'cta',
                    ])
                )
            );

            $card['bg'] = $original['bg'];

            $defaultRoute = $index === 0
                ? 'experience'
                : 'social';

            $card['href'] = $this->cmsUrl(
                $saved['href'] ?? null,
                $serviceUrls[$defaultRoute],
                $query
            );

            $relatedCards[] = $card;
        }

        $t['perf']['related'] = $relatedCards;

        // ------------------------------------
        // SEO DE PERFORMANCE
        // ------------------------------------

        $seo = $read('seo');

        $defaultTitle = 'Performance & Connections — Feat.';

        $metaTitle = trim(
            (string) ($seo['meta_title'] ?? '')
        );

        if ($metaTitle === '') {
            $metaTitle = $defaultTitle;
        }

        $metaDescription = trim(
            (string) ($seo['meta_description'] ?? '')
        );

        if ($metaDescription === '') {
            $metaDescription = $t['perf']['answer'];
        }

        $canonical = trim(
            (string) ($seo['canonical_url'] ?? '')
        );

        $canonicalScheme = strtolower(
            (string) parse_url(
                $canonical,
                PHP_URL_SCHEME
            )
        );

        if (
            !filter_var($canonical, FILTER_VALIDATE_URL)
            || !in_array(
                $canonicalScheme,
                ['http', 'https'],
                true
            )
        ) {
            $canonical = route(
                'performance',
                ['lang' => $locale]
            );
        }

        $pageSeo = [
            'title' => $metaTitle,

            'description' => $metaDescription,

            'canonical' => $canonical,

            'og_title' => filled($seo['og_title'] ?? null)
                ? $seo['og_title']
                : $metaTitle,

            'og_description' => filled(
                $seo['og_description'] ?? null
            )
                ? $seo['og_description']
                : $metaDescription,

            'og_image' => $this->absoluteImageUrl(
                $seo['og_image'] ?? null
            ),

            'og_image_alt' => $seo['og_image_alt'] ?? '',

            'robots' => (
                ($seo['robots_index'] ?? true)
                    ? 'index'
                    : 'noindex'
            ) . ', ' . (
                ($seo['robots_follow'] ?? true)
                    ? 'follow'
                    : 'nofollow'
            ),

            'og_locale' => $locale === 'es'
                ? 'es_LA'
                : 'en_US',

            'alternates' => [
                'es' => route(
                    'performance',
                    ['lang' => 'es']
                ),

                'en' => route(
                    'performance',
                    ['lang' => 'en']
                ),
            ],
        ];

        return [$t, $pageSeo];
    }
}
