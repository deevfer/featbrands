<?php

namespace App\Services;

use App\Models\PageSection;
use Illuminate\Support\Facades\Storage;

/**
 * Every icon of the public site. Each one can be replaced by another symbol or by an
 * image; when nothing is saved, the original one written in the template is shown.
 */
class SiteIcons
{
    public const SECTION = 'icons';

    /**
     * Icons grouped by the page where they are seen. A null default is a drawn icon
     * (inline SVG or illustration) that only lives in the template.
     *
     * @var array<string, array{label: string, url: string, icons: array<string, array{0: string, 1: string|null}>}>
     */
    public const GROUPS = [
        'general' => [
            'label' => 'Generales',
            'url' => '/',
            'icons' => [
                'arrow_out' => ['Flecha de los botones y enlaces (todo el sitio)', '↗'],
                'arrow_next' => ['Flecha "siguiente"', '→'],
                'arrow_back' => ['Flecha "anterior" y "volver"', '←'],
                'arrow_down' => ['Flecha hacia abajo', '↓'],
                'caret' => ['Flecha del menú Servicios', '▾'],
                'office_open' => ['Oficina abierta (Home y Nosotros)', '–'],
                'office_closed' => ['Oficina cerrada (Home y Nosotros)', '+'],
            ],
        ],
        'home' => [
            'label' => 'Home',
            'url' => '/',
            'icons' => [
                'service_performance' => ['Tarjeta Performance & Connections', null],
                'service_brand' => ['Tarjeta Brand & Consulting', null],
                'service_experience' => ['Tarjeta Experience & Tech', null],
                'service_makitha' => ['Tarjeta Makitha', null],
                'service_social' => ['Tarjeta Social Media Strategies', null],
                'play' => ['Botón de reproducir video', null],
            ],
        ],
        'brand' => [
            'label' => 'Brand',
            'url' => '/brand-consulting',
            'icons' => [
                'workshop' => ['Asterisco del taller', '✳'],
                'specimen' => ['Flecha de la muestra tipográfica', '↗'],
            ],
        ],
        'experience' => [
            'label' => 'Experience',
            'url' => '/experience-tech',
            'icons' => [
                'cursor' => ['Cursor de la portada', null],
                'window_dots' => ['Puntos de la ventana del navegador', '● ● ●'],
                'screen_1' => ['Pantalla del paso 1', '↗'],
                'screen_2' => ['Pantalla del paso 2', '✓'],
                'screen_3' => ['Pantalla del paso 3', '→'],
                'connect_1' => ['Conexión 1', '□'],
                'connect_2' => ['Conexión 2', '✳'],
                'connect_3' => ['Conexión 3', '⌘'],
            ],
        ],
        'makitha' => [
            'label' => 'Makitha',
            'url' => '/makitha',
            'icons' => [
                'engine_1' => ['Motor: nodo 1', '⌘'],
                'engine_2' => ['Motor: nodo 2', '↗'],
                'engine_3' => ['Motor: nodo 3', '↔'],
                'engine_4' => ['Motor: nodo 4', '✳'],
                'engine_center' => ['Motor: centro', '↗'],
                'window_dots' => ['Puntos de las ventanas de soluciones', '● ● ●'],
                'web' => ['Solución Web', '↗'],
                'app' => ['Solución Apps', '⌘'],
                'growth' => ['Solución Growth', '↗'],
                'integration' => ['Solución Integraciones', '↔'],
                'data' => ['Solución Data', '↗'],
                'ai_in' => ['Solución IA: entrada', '↳'],
                'ai_out' => ['Solución IA: salida', '↗'],
            ],
        ],
        'social' => [
            'label' => 'Social Media',
            'url' => '/social-media-strategies',
            'icons' => [
                'window_dots' => ['Puntos de la publicación', '● ● ●'],
                'post_like' => ['Me gusta de la publicación', '♡ ↗'],
                'post_save' => ['Guardar de la publicación', '⌑'],
                'reply_quote' => ['Comillas de la respuesta', '“'],
                'reply' => ['Flecha de la respuesta', '↳'],
                'mode_1' => ['Ilustración del modo 1', '!'],
                'mode_2_open' => ['Ilustración del modo 2 (comilla de apertura)', '“'],
                'mode_2_close' => ['Ilustración del modo 2 (comilla de cierre)', '”'],
                'mode_3' => ['Ilustración del modo 3', '↗'],
                'rhythm_step' => ['Flecha entre pasos del ritmo', '→'],
                'rhythm_last' => ['Último paso del ritmo', '↺'],
                'metric' => ['Viñeta de las métricas', '↳'],
            ],
        ],
        'cases' => [
            'label' => 'Casos',
            'url' => '/casos',
            'icons' => [
                'social_quote' => ['Comillas del caso social', '“'],
                'social_bubble' => ['Burbuja del caso social', '↗'],
                'data' => ['Flecha del caso de datos', '↗'],
            ],
        ],
        'about' => [
            'label' => 'Nosotros',
            'url' => '/nosotros',
            'icons' => [
                'manifesto' => ['Asterisco del manifiesto', '✳'],
                'office_gt' => ['Ilustración de Guatemala', null],
                'office_cr' => ['Ilustración de Costa Rica', null],
                'office_sv' => ['Ilustración de El Salvador', null],
                'office_us' => ['Ilustración de Estados Unidos', null],
            ],
        ],
        'contact' => [
            'label' => 'Contacto',
            'url' => '/contacto',
            'icons' => [
                'select' => ['Flecha del selector de país', null],
                'submit' => ['Flecha del botón de envío', null],
                'success' => ['Mensaje enviado', '↗'],
            ],
        ],
        'blog' => [
            'label' => 'Blog',
            'url' => '/blog',
            'icons' => [
                'cover_star' => ['Asterisco de la portada', '✳'],
                'search' => ['Lupa del buscador', null],
                'card_art' => ['Flecha de las tarjetas sin imagen', '↗'],
                'empty' => ['Sin resultados', '↗'],
                'share_linkedin' => ['Compartir en LinkedIn', 'in'],
                'share_facebook' => ['Compartir en Facebook', 'f'],
                'share_x' => ['Compartir en X', 'X'],
            ],
        ],
    ];

    /** @var array<string, array<string, array{text?: string, image?: string}>>|null */
    private ?array $saved = null;

    /**
     * Icons saved from the admin, by group and icon.
     *
     * @return array<string, array<string, array{text?: string, image?: string}>>
     */
    public function stored(): array
    {
        if ($this->saved === null) {
            $saved = PageSection::query()->where('page', SiteContent::PAGE)->where('section', self::SECTION)->value('content_es');
            $saved = is_string($saved) ? json_decode($saved, true) : $saved;
            $this->saved = is_array($saved) ? $saved : [];
        }

        return $this->saved;
    }

    /**
     * Keeps only known icons and the values actually filled in.
     *
     * @param  array<string, array<string, array{text?: string|null, image?: string|null}>>  $icons
     */
    public function save(array $icons): void
    {
        $values = [];
        foreach (self::GROUPS as $group => $definition) {
            foreach (array_keys($definition['icons']) as $key) {
                $icon = array_filter([
                    'text' => trim((string) data_get($icons, "{$group}.{$key}.text")),
                    'image' => (string) data_get($icons, "{$group}.{$key}.image"),
                ], 'filled');

                if ($icon !== []) {
                    $values[$group][$key] = $icon;
                }
            }
        }

        PageSection::updateOrCreate(
            ['page' => SiteContent::PAGE, 'section' => self::SECTION],
            ['content_es' => $values, 'content_en' => []],
        );

        $this->saved = null;
    }

    /**
     * What replaces the original icon ("group.icon"): an image URL, a symbol, or nulls for the original.
     *
     * @return array{image: string|null, text: string|null}
     */
    public function resolve(string $name): array
    {
        $icon = data_get($this->stored(), $name, []);
        $image = $icon['image'] ?? null;

        return [
            'image' => filled($image) && Storage::disk('public')->exists($image) ? Storage::disk('public')->url($image) : null,
            'text' => filled($icon['text'] ?? null) ? $icon['text'] : null,
        ];
    }
}
