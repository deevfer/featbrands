<?php

namespace App\Services;

use App\Models\PageSection;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

/**
 * Editable copy for the pages without a dedicated CMS: the JSON files stay as the original
 * content and whatever is saved from the admin (page_sections, page "content") replaces it.
 */
class SiteContent
{
    public const PAGE = 'content';

    /**
     * url: public page that shows it. file: JSON under resources/data. path: key inside the locale (or the file, when not localized).
     * only: top-level keys exposed. groups: form sections. growable: lists that accept new items.
     * locked: item keys that drive the layout and cannot be edited. columns: labels of [a, b] pairs.
     *
     * @var array<string, array{url: string, label: string, file: string, path?: string, localized?: bool, only?: array<int, string>, groups: array<string, array<int, string>>, growable?: array<int, string>, locked?: array<int, string>, columns?: array<string, array<int, string>>}>
     */
    public const SOURCES = [
        'global' => [
            'url' => '/',
            'label' => 'Menú y footer',
            'file' => 'site.json',
            'only' => ['nav', 'cta', 'skipLink', 'servicesBtn', 'explore', 'demoTag', 'addrLabel', 'phoneLabel', 'newBusinessLabel', 'footer', 'cookies'],
            'groups' => [
                'Menú' => ['nav', 'cta', 'skipLink'],
                'Footer' => ['footer'],
                'Aviso de cookies' => ['cookies'],
                'Etiquetas compartidas' => ['servicesBtn', 'explore', 'demoTag', 'addrLabel', 'phoneLabel', 'newBusinessLabel'],
            ],
        ],
        'social' => [
            'url' => '/social-media-strategies',
            'label' => 'Social Media',
            'file' => 'specializations.json',
            'path' => 'social',
            'groups' => [
                'Portada' => ['name', 'kicker', 'title', 'accent', 'intro', 'cta', 'ctaHref'],
                'Collage' => ['postLabel', 'postWords', 'reply', 'replyAccent', 'stickerTop', 'stickerAccent', 'postFoot'],
                'Laboratorio' => ['labKicker', 'labTitle', 'labBody', 'exampleLabel', 'modes'],
                'Ritmo' => ['rhythmKicker', 'rhythmTitle', 'rhythm'],
                'Medición' => ['measureKicker', 'measureTitle', 'measureStrike', 'measureWord', 'measureBody', 'metrics'],
                'Cierre' => ['closingQuestion', 'closingAnswer', 'closingCta', 'closingHref', 'next', 'nextName', 'nextHref'],
            ],
            'growable' => ['rhythm', 'metrics'],
            'columns' => ['rhythm' => ['Paso', 'Descripción'], 'postWords' => ['Texto']],
        ],
        'cases' => [
            'url' => '/casos',
            'label' => 'Casos',
            'file' => 'agency.json',
            'path' => 'cases',
            'groups' => [
                'Portada' => ['name', 'kicker', 'title', 'accent', 'intro', 'aboutLink', 'aboutHref', 'heroNote'],
                'Archivo' => ['archiveKicker', 'archiveTitle', 'archiveIntro', 'filterLabel', 'filters', 'demoNote', 'countSingular', 'countPlural', 'empty'],
                'Casos' => ['studies', 'openLabel', 'closeLabel', 'challengeLabel', 'approachLabel', 'resultLabel', 'serviceLabel'],
                'Cierre' => ['closingKicker', 'closing', 'closingBody', 'closingCta', 'closingHref'],
            ],
            'locked' => ['filters.*.0', 'studies.*.category', 'studies.*.service'],
            'columns' => ['filters' => ['Clave del filtro', 'Texto'], 'artWords' => ['Palabra']],
        ],
        'about' => [
            'url' => '/nosotros',
            'label' => 'Nosotros',
            'file' => 'agency.json',
            'path' => 'about',
            'groups' => [
                'Portada' => ['name', 'kicker', 'title', 'accent', 'intro', 'heroNote', 'scrollLabel'],
                'Manifiesto' => ['manifestoKicker', 'manifesto', 'manifestoBody', 'principles'],
                'Especialidades' => ['servicesKicker', 'servicesTitle', 'servicesBody', 'serviceCta'],
                'Marcas, premios y presencia' => ['clientsKicker', 'clientsBody', 'awardsKicker', 'awardsTitle', 'marketsKicker', 'marketsTitle', 'marketsBody'],
                'Cierre' => ['closing', 'closingCta', 'closingHref', 'workLink', 'workName', 'workHref'],
            ],
            'growable' => ['principles'],
            'columns' => ['principles' => ['Principio', 'Descripción']],
        ],
        'contact' => [
            'url' => '/contacto',
            'label' => 'Contacto',
            'file' => 'contact.json',
            'groups' => [
                'Portada' => ['name', 'asideKicker', 'title', 'accent', 'intro', 'note', 'sideFoot'],
                'Formulario' => ['kicker', 'formTitle', 'formIntro', 'requiredNote', 'personal', 'project', 'optional', 'choose', 'fields', 'placeholders', 'phoneHint', 'messageHint', 'referrals', 'send', 'sending'],
                'Mensajes' => ['successTitle', 'success', 'again', 'errorTitle', 'networkError', 'rateLimit', 'expired', 'invalidField', 'captchaError', 'validation'],
            ],
        ],
        'email_confirmation' => [
            'url' => '/admin/correos/vista-previa/confirmacion',
            'label' => 'Confirmación al cliente',
            'file' => 'emails.json',
            'path' => 'confirmation',
            'groups' => [
                'Encabezado' => ['subject', 'preheader', 'eyebrow', 'title', 'accent'],
                'Mensaje' => ['greeting', 'body', 'summaryTitle', 'replyNote', 'cta', 'ctaHref', 'signature', 'footer'],
            ],
        ],
        'email_lead' => [
            'url' => '/admin/correos/vista-previa/lead',
            'label' => 'Aviso al equipo',
            'file' => 'emails.json',
            'path' => 'lead',
            'localized' => false,
            'groups' => [
                'Correo' => ['subject', 'eyebrow', 'title', 'intro', 'cta', 'replyNote', 'footer'],
            ],
        ],
        'email_abandoned' => [
            'url' => '/admin/correos/vista-previa/sin-completar',
            'label' => 'Aviso de formulario sin completar',
            'file' => 'emails.json',
            'path' => 'abandoned',
            'localized' => false,
            'groups' => [
                'Correo' => ['subject', 'eyebrow', 'title', 'intro', 'trackingLabel', 'noTracking', 'cta', 'replyNote', 'footer'],
            ],
        ],
        'blog' => [
            'url' => '/blog',
            'label' => 'Textos del blog',
            'file' => 'blog/ui.json',
            'groups' => [
                'Portada' => ['name', 'kicker', 'masthead', 'title', 'accent', 'intro', 'note', 'archive'],
                'Archivo y búsqueda' => ['all', 'filter', 'search', 'searchPlaceholder', 'searchButton', 'article', 'articles', 'results', 'tag', 'empty', 'emptyBody', 'clear', 'previous', 'next', 'page', 'of'],
                'Tarjetas' => ['read', 'minutes', 'featured', 'cardLabel', 'noImage', 'noImageFoot', 'published', 'updated'],
                'Artículo' => ['back', 'writtenBy', 'contents', 'share', 'copyLink', 'copied', 'print', 'relatedKicker', 'related', 'originalLanguage', 'originalSpanish'],
                'Cierre' => ['ctaKicker', 'cta', 'ctaButton', 'ctaHref'],
            ],
        ],
        'mejor-agencia-publicidad-guatemala' => [
            'url' => '/mejor-agencia-publicidad-guatemala',
            'label' => 'Mejor agencia de publicidad',
            'file' => 'geo.json',
            'path' => 'pages.mejor-agencia-publicidad-guatemala',
            'localized' => false,
            'only' => ['h1', 'body'],
            'groups' => ['Contenido' => ['h1', 'body']],
            'locked' => ['body.*.type'],
            'growable' => ['body.*.items', 'body.*.links'],
        ],
        'mejorar-comunicacion-marca-guatemala' => [
            'url' => '/mejorar-comunicacion-marca-guatemala',
            'label' => 'Comunicación de marca',
            'file' => 'geo.json',
            'path' => 'pages.mejorar-comunicacion-marca-guatemala',
            'localized' => false,
            'only' => ['h1', 'body'],
            'groups' => ['Contenido' => ['h1', 'body']],
            'locked' => ['body.*.type'],
            'growable' => ['body.*.items', 'body.*.links'],
        ],
        'publicidad-digital-guatemala-city' => [
            'url' => '/publicidad-digital-guatemala-city',
            'label' => 'Publicidad digital Guatemala City',
            'file' => 'geo.json',
            'path' => 'pages.publicidad-digital-guatemala-city',
            'localized' => false,
            'only' => ['h1', 'body'],
            'groups' => ['Contenido' => ['h1', 'body']],
            'locked' => ['body.*.type'],
            'growable' => ['body.*.items', 'body.*.links'],
        ],
    ];

    /** Logos used across the layout, stored once for both languages. */
    public const MEDIA = 'global-media';

    /** @return array<int, string> */
    public function locales(string $key): array
    {
        return (self::SOURCES[$key]['localized'] ?? true) ? ['es', 'en'] : ['es'];
    }

    /**
     * Original copy from the JSON file.
     *
     * @return array<string, mixed>
     */
    public function defaults(string $key, string $locale): array
    {
        $source = self::SOURCES[$key];
        $json = File::json(resource_path('data/'.$source['file']));
        $path = ($source['localized'] ?? true) ? trim($locale.'.'.($source['path'] ?? ''), '.') : $source['path'];
        $copy = data_get($json, $path) ?? [];

        return isset($source['only']) ? Arr::only($copy, $source['only']) : $copy;
    }

    /**
     * Copy saved from the admin, as stored.
     *
     * @return array<string, mixed>
     */
    public function stored(string $key, string $locale): array
    {
        $saved = PageSection::query()->where('page', self::PAGE)->where('section', $key)->value('content_'.$locale);
        $saved = is_string($saved) ? json_decode($saved, true) : $saved;

        return is_array($saved) ? $saved : [];
    }

    /**
     * Copy to render: the saved values over the original ones.
     *
     * @return array<string, mixed>
     */
    public function get(string $key, string $locale): array
    {
        $locale = in_array($locale, $this->locales($key), true) ? $locale : 'es';

        return self::merge($this->defaults($key, $locale), $this->stored($key, $locale));
    }

    /**
     * @param  array<string, array<string, mixed>>  $copyByLocale
     */
    public function save(string $key, array $copyByLocale): void
    {
        $values = [];
        foreach ($this->locales($key) as $locale) {
            $values['content_'.$locale] = self::merge($this->defaults($key, $locale), $copyByLocale[$locale] ?? []);
        }

        PageSection::updateOrCreate(['page' => self::PAGE, 'section' => $key], $values);
    }

    /**
     * Logos of the layout, falling back to the original files.
     *
     * @return array{logo_light: string, logo_dark: string, favicon: string}
     */
    public function media(): array
    {
        $saved = PageSection::query()->where('page', self::PAGE)->where('section', self::MEDIA)->first()?->content_es ?? [];
        $url = fn (string $field, string $fallback): string => filled($saved[$field] ?? null) && Storage::disk('public')->exists($saved[$field])
            ? Storage::disk('public')->url($saved[$field])
            : asset($fallback);

        return [
            'logo_light' => $url('logo_light', 'assets/feat-white.svg'),
            'logo_dark' => $url('logo_dark', 'assets/feat-black.svg'),
            'favicon' => $url('favicon', 'assets/feat-black.svg'),
        ];
    }

    /**
     * Keeps the original structure: blank or missing values fall back to the original, lists
     * take the saved items (new items start from an empty copy of the first original item).
     */
    public static function merge(mixed $default, mixed $stored): mixed
    {
        if (is_array($default) && array_is_list($default) && $default !== []) {
            if (! is_array($stored) || ! array_is_list($stored) || $stored === []) {
                return $default;
            }

            return array_map(
                fn (mixed $item, int $index): mixed => self::merge($default[$index] ?? self::blank($default[0]), $item),
                $stored,
                array_keys($stored),
            );
        }

        if (is_array($default)) {
            if (! is_array($stored)) {
                return $default;
            }
            foreach ($default as $field => $value) {
                if (array_key_exists($field, $stored)) {
                    $default[$field] = self::merge($value, $stored[$field]);
                }
            }

            return $default;
        }

        return is_string($stored) && trim($stored) !== '' ? $stored : $default;
    }

    private static function blank(mixed $value): mixed
    {
        return is_array($value) ? array_map(self::blank(...), $value) : '';
    }
}
