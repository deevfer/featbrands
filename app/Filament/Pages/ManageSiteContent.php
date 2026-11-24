<?php

namespace App\Filament\Pages;

use App\Filament\Concerns\HasLivePreview;
use App\Models\PageSection;
use App\Services\SiteContent;
use Filament\Actions\Action;
use Filament\Forms\Components\Field;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Form generated from the structure of the original JSON copy (see SiteContent::SOURCES):
 * every text, list and link of the page is editable without a hand-written form.
 */
abstract class ManageSiteContent extends Page
{
    use HasLivePreview;

    protected string $view = 'filament.pages.manage-site-content';

    /** @var array<int, string> SiteContent::SOURCES keys edited on this page. */
    protected static array $contentKeys = [];

    public ?array $data = [];

    private const LABELS = [
        'cta' => 'Botón', 'ctaHref' => 'Enlace del botón', 'ctaButton' => 'Texto del botón', 'ctaKicker' => 'Antetítulo del cierre',
        'skipLink' => 'Enlace "Ir al contenido" (accesibilidad)', 'nav' => 'Menú', 'servicesBtn' => 'Botón de las especialidades',
        'explore' => 'Texto "Ver"', 'demoTag' => 'Etiqueta de caso demostrativo', 'addrLabel' => 'Etiqueta de dirección',
        'phoneLabel' => 'Etiqueta de teléfono', 'newBusinessLabel' => 'Etiqueta de nuevos negocios', 'footer' => 'Footer',
        'tagline' => 'Frase', 'cols' => 'Columnas', 'head' => 'Título de la columna', 'items' => 'Elementos', 'hrefs' => 'Enlaces de los elementos',
        'legal' => 'Texto legal', 'ipCredit' => 'Crédito de geolocalización (DB-IP)', 'offices' => 'Oficinas', 'name' => 'Nombre de la página', 'intro' => 'Introducción',
        'kicker' => 'Antetítulo', 'title' => 'Título', 'accent' => 'Título (parte destacada)', 'note' => 'Nota',
        'postLabel' => 'Descripción del collage', 'postWords' => 'Texto de la publicación', 'reply' => 'Respuesta', 'replyAccent' => 'Respuesta (parte destacada)',
        'stickerTop' => 'Sticker (línea 1)', 'stickerAccent' => 'Sticker (línea 2)', 'postFoot' => 'Pie del collage',
        'labKicker' => 'Antetítulo', 'labTitle' => 'Título', 'labBody' => 'Texto', 'exampleLabel' => 'Firma de los ejemplos', 'modes' => 'Modos',
        'label' => 'Etiqueta', 'tag' => 'Etiqueta', 'headline' => 'Titular', 'body' => 'Texto', 'caption' => 'Frase', 'format' => 'Formatos',
        'rhythmKicker' => 'Antetítulo', 'rhythmTitle' => 'Título', 'rhythm' => 'Pasos',
        'measureKicker' => 'Antetítulo', 'measureTitle' => 'Título', 'measureStrike' => 'Palabra tachada', 'measureWord' => 'Palabra destacada',
        'measureBody' => 'Texto', 'metrics' => 'Métricas', 'closingQuestion' => 'Pregunta de cierre', 'closingAnswer' => 'Respuesta de cierre',
        'closingCta' => 'Botón de cierre', 'closingHref' => 'Enlace del botón de cierre', 'closingKicker' => 'Antetítulo del cierre',
        'closing' => 'Frase de cierre', 'closingBody' => 'Texto de cierre', 'next' => 'Texto "Siguiente"', 'nextName' => 'Nombre de la siguiente página',
        'nextHref' => 'Enlace de la siguiente página', 'heroNote' => 'Nota de portada', 'aboutLink' => 'Enlace a Nosotros (texto)',
        'aboutHref' => 'Enlace a Nosotros', 'archiveKicker' => 'Antetítulo', 'archiveTitle' => 'Título', 'archiveIntro' => 'Introducción',
        'filterLabel' => 'Descripción de los filtros', 'filters' => 'Filtros', 'demoNote' => 'Nota de casos demostrativos',
        'countSingular' => 'Contador (singular)', 'countPlural' => 'Contador (plural)', 'openLabel' => 'Texto "Abrir"', 'closeLabel' => 'Texto "Cerrar"',
        'challengeLabel' => 'Etiqueta del reto', 'approachLabel' => 'Etiqueta del enfoque', 'resultLabel' => 'Etiqueta del resultado',
        'serviceLabel' => 'Enlace a la especialidad (texto)', 'empty' => 'Mensaje sin resultados', 'studies' => 'Casos', 'category' => 'Categoría',
        'categoryLabel' => 'Etiqueta de la categoría', 'artWords' => 'Palabras de la ilustración', 'challenge' => 'Reto', 'approach' => 'Enfoque',
        'service' => 'Especialidad', 'scrollLabel' => 'Texto para bajar', 'manifestoKicker' => 'Antetítulo', 'manifesto' => 'Manifiesto',
        'manifestoBody' => 'Texto', 'principles' => 'Principios', 'servicesKicker' => 'Antetítulo', 'servicesTitle' => 'Título',
        'servicesBody' => 'Texto', 'serviceCta' => 'Texto de los enlaces', 'clientsKicker' => 'Antetítulo de marcas', 'clientsBody' => 'Texto de marcas',
        'awardsKicker' => 'Antetítulo de premios', 'awardsTitle' => 'Título de premios', 'marketsKicker' => 'Antetítulo de presencia',
        'marketsTitle' => 'Título de presencia', 'marketsBody' => 'Texto de presencia', 'workLink' => 'Texto antes del enlace a casos',
        'workName' => 'Enlace a casos (texto)', 'workHref' => 'Enlace a casos', 'formTitle' => 'Título del formulario', 'formIntro' => 'Introducción del formulario',
        'requiredNote' => 'Nota de campos obligatorios', 'personal' => 'Título de datos personales', 'project' => 'Título del proyecto',
        'optional' => 'Texto "opcional"', 'choose' => 'Texto "Selecciona una opción"', 'fields' => 'Nombres de los campos', 'placeholders' => 'Ejemplos dentro de los campos',
        'first_name' => 'Nombre', 'last_name' => 'Apellido', 'email' => 'Correo', 'country_code' => 'Prefijo', 'phone' => 'Teléfono',
        'company' => 'Empresa', 'job_title' => 'Cargo', 'budget' => 'Presupuesto', 'referral_source' => '¿Cómo nos conociste?',
        'message' => 'Mensaje', 'phone_country' => 'País del teléfono', 'phoneHint' => 'Ayuda del teléfono', 'messageHint' => 'Ayuda del mensaje',
        'send' => 'Botón de envío', 'sending' => 'Botón mientras envía', 'successTitle' => 'Título de éxito', 'success' => 'Mensaje de éxito',
        'again' => 'Botón "Enviar otra"', 'errorTitle' => 'Error de validación', 'networkError' => 'Error de conexión', 'rateLimit' => 'Demasiados intentos',
        'expired' => 'Sesión vencida', 'invalidField' => 'Campo inválido', 'asideKicker' => 'Antetítulo lateral', 'asideTitle' => 'Título lateral',
        'asideBody' => 'Texto lateral', 'sideFoot' => 'Pie lateral', 'referrals' => 'Opciones de "¿Cómo nos conociste?"',
        'recommendation' => 'Recomendación', 'google' => 'Google', 'instagram' => 'Instagram', 'linkedin' => 'LinkedIn', 'event' => 'Evento',
        'client' => 'Cliente', 'other' => 'Otro', 'validation' => 'Mensajes de validación', 'required' => 'Obligatorio', 'string' => 'Texto',
        'max' => 'Máximo', 'min' => 'Mínimo', 'in' => 'Opción inválida', 'regex' => 'Formato inválido', 'honeypot' => 'Envío bloqueado',
        'masthead' => 'Firma de la portada', 'all' => 'Texto "Todos"', 'search' => 'Etiqueta de búsqueda', 'searchPlaceholder' => 'Ejemplo de búsqueda',
        'searchButton' => 'Botón de búsqueda', 'archive' => 'Enlace al archivo', 'article' => 'Artículo (singular)', 'articles' => 'Artículos (plural)',
        'read' => 'Texto "Leer"', 'minutes' => 'Texto "min"', 'featured' => 'Etiqueta destacada', 'cardLabel' => 'Firma de las tarjetas',
        'filter' => 'Descripción de las categorías', 'emptyBody' => 'Texto sin resultados', 'clear' => 'Botón para limpiar', 'results' => 'Texto "Resultados"',
        'previous' => 'Texto "Anterior"', 'page' => 'Texto "Página"', 'of' => 'Texto "de"', 'back' => 'Enlace de regreso', 'writtenBy' => 'Texto "Escrito por"',
        'contents' => 'Título del índice', 'share' => 'Texto "Compartir"', 'copyLink' => 'Botón "Copiar enlace"', 'copied' => 'Texto "Copiado"',
        'print' => 'Botón "Imprimir"', 'related' => 'Título de relacionados', 'relatedKicker' => 'Antetítulo de relacionados',
        'originalLanguage' => 'Aviso de idioma original', 'originalSpanish' => 'Aviso de original en español', 'noImage' => 'Texto sin imagen',
        'noImageFoot' => 'Pie sin imagen', 'published' => 'Texto "Publicado"', 'updated' => 'Texto "Actualizado"', 'h1' => 'Título (H1)',
        'type' => 'Tipo de bloque', 'text' => 'Texto', 'links' => 'Enlaces', 'href' => 'Enlace',
        'captchaError' => 'Error de verificación (reCAPTCHA)', 'subject' => 'Asunto del correo', 'preheader' => 'Texto previo en la bandeja de entrada',
        'eyebrow' => 'Antetítulo', 'greeting' => 'Saludo', 'summaryTitle' => 'Título del resumen', 'replyNote' => 'Nota para responder', 'trackingLabel' => 'Título de las cookies de retargeting', 'noTracking' => 'Texto si no aceptó cookies de marketing',
        'signature' => 'Firma',
        'cookies' => 'Aviso de cookies', 'trigger' => 'Botón para reabrir', 'essentialTitle' => 'Necesarias (título)', 'essentialBody' => 'Necesarias (texto)',
        'analyticsTitle' => 'Analítica (título)', 'analyticsBody' => 'Analítica (texto)', 'marketingTitle' => 'Marketing (título)', 'marketingBody' => 'Marketing (texto)',
        'accept' => 'Botón "Aceptar todas"', 'reject' => 'Botón "Solo necesarias"', 'configure' => 'Botón "Configurar"', 'save' => 'Botón "Guardar"',
        'close' => 'Botón "Cerrar" (accesibilidad)',
    ];

    public function mount(): void
    {
        $content = app(SiteContent::class);
        $state = [];

        foreach (static::$contentKeys as $index => $key) {
            foreach ($content->locales($key) as $locale) {
                $state["c{$index}"][$locale] = $this->toForm($content->get($key, $locale), $content->defaults($key, $locale));
            }
        }

        if ($this->mediaSchema() !== []) {
            $state['media'] = PageSection::query()->where('page', SiteContent::PAGE)->where('section', SiteContent::MEDIA)->first()?->content_es ?? [];
        }

        $this->form->fill($state);
    }

    public function form(Schema $schema): Schema
    {
        $content = app(SiteContent::class);
        $tabs = [];

        foreach (static::$contentKeys as $index => $key) {
            $languages = array_map(
                fn (string $locale): Tab => Tab::make($locale === 'es' ? 'Español' : 'Inglés')->schema([
                    Group::make($this->groups($key, $content->defaults($key, $locale)))->statePath("c{$index}.{$locale}"),
                ]),
                $content->locales($key),
            );

            $tabs = count(static::$contentKeys) === 1
                ? $languages
                : [...$tabs, Tab::make(SiteContent::SOURCES[$key]['label'])->schema([Tabs::make("Idiomas {$key}")->tabs($languages)])];
        }

        if ($this->mediaSchema() !== []) {
            $tabs[] = Tab::make('Logos')->schema([Group::make($this->mediaSchema())->statePath('media')]);
        }

        return $schema
            ->components([
                Form::make([Tabs::make(static::$title)->tabs($tabs)])
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
        $state = $this->form->getState();
        $content = app(SiteContent::class);

        DB::transaction(function () use ($state, $content): void {
            foreach (static::$contentKeys as $index => $key) {
                $copy = [];
                foreach ($content->locales($key) as $locale) {
                    $copy[$locale] = $this->fromForm($state["c{$index}"][$locale] ?? [], $content->defaults($key, $locale));
                }
                $content->save($key, $copy);
            }

            if ($this->mediaSchema() !== []) {
                PageSection::updateOrCreate(
                    ['page' => SiteContent::PAGE, 'section' => SiteContent::MEDIA],
                    ['content_es' => $state['media'] ?? [], 'content_en' => []],
                );
            }
        });

        Notification::make()->title('Cambios guardados')->success()->send();
    }

    protected function previewPath(?string $statePath = null): string
    {
        $index = preg_match('/(?:^|\.)c(\d+)\./', (string) $statePath, $match) ? (int) $match[1] : 0;

        return SiteContent::SOURCES[static::$contentKeys[$index] ?? static::$contentKeys[0]]['url'];
    }

    protected function previewIsLocalized(): bool
    {
        return count(app(SiteContent::class)->locales(static::$contentKeys[0])) > 1;
    }

    /**
     * Fields stored once for both languages (logos).
     *
     * @return array<int, Component|Field>
     */
    protected function mediaSchema(): array
    {
        return [];
    }

    /**
     * @param  array<string, mixed>  $defaults
     * @return array<int, Section>
     */
    private function groups(string $key, array $defaults): array
    {
        $source = SiteContent::SOURCES[$key];
        $sections = [];
        $grouped = [];

        foreach ($source['groups'] as $label => $fields) {
            $fields = array_values(array_filter($fields, fn (string $field): bool => array_key_exists($field, $defaults)));
            $grouped = [...$grouped, ...$fields];
            $sections[] = Section::make($label)->schema($this->fields(array_intersect_key($defaults, array_flip($fields)), '', $source))->collapsible();
        }

        $others = array_diff_key($defaults, array_flip($grouped));
        if ($others !== []) {
            $sections[] = Section::make('Otros textos')->schema($this->fields($others, '', $source))->collapsible()->collapsed();
        }

        return $sections;
    }

    /**
     * @param  array<string|int, mixed>  $defaults
     * @param  array<string, mixed>  $source
     * @return array<int, Component|Field>
     */
    private function fields(array $defaults, string $path, array $source): array
    {
        $components = [];

        foreach ($defaults as $name => $value) {
            $name = (string) $name;
            $fieldPath = ltrim($path.'.'.$name, '.');
            $label = self::LABELS[$name] ?? Str::headline($name);

            if (! is_array($value)) {
                $components[] = $this->textField($name, $label, (string) $value, $this->matches($fieldPath, $source['locked'] ?? []));

                continue;
            }

            if (! array_is_list($value)) {
                $components[] = Section::make($label)->statePath($name)->schema($this->fields($value, $fieldPath, $source))->compact()->collapsible();

                continue;
            }

            $growable = $this->matches($fieldPath, $source['growable'] ?? []);
            $repeater = Repeater::make($name)
                ->label($label)
                ->addable($growable)
                ->deletable($growable)
                ->reorderable($growable)
                ->defaultItems(0);

            $first = $value[0] ?? '';
            if (! is_array($first)) {
                $components[] = $repeater->simple($this->textField('value', '', (string) $first, false, $name === 'hrefs'));

                continue;
            }

            if (array_is_list($first)) {
                $columns = $source['columns'][$name] ?? [];
                $components[] = $repeater->schema(array_map(
                    fn (int $column): Field => $this->textField('c'.$column, $columns[$column] ?? 'Texto '.($column + 1), (string) ($first[$column] ?? ''), $this->matches($fieldPath.'.*.'.$column, $source['locked'] ?? [])),
                    array_keys($first),
                ))->columns(count($first) > 1 && max(array_map(fn (mixed $item): int => mb_strlen((string) $item), $first)) < 90 ? count($first) : 1);

                continue;
            }

            $components[] = $repeater
                ->schema($this->itemFields($value, $fieldPath.'.*', $source))
                ->itemLabel(fn (array $state): ?string => Str::limit((string) ($state['title'] ?? $state['label'] ?? $state['text'] ?? $state['h1'] ?? $state['type'] ?? ''), 60))
                ->collapsible();
        }

        return $components;
    }

    /**
     * Fields of a list of objects; when items have a "type", each field shows only on the types that use it.
     *
     * @param  array<int, array<string, mixed>>  $items
     * @param  array<string, mixed>  $source
     * @return array<int, Component|Field>
     */
    private function itemFields(array $items, string $path, array $source): array
    {
        $template = array_replace(...array_map(fn (array $item): array => $item, array_reverse($items)));
        $fieldsByType = [];
        foreach ($items as $item) {
            if (isset($item['type'])) {
                $fieldsByType[$item['type']] = [...($fieldsByType[$item['type']] ?? []), ...array_keys($item)];
            }
        }

        return array_map(function (Component|Field $component) use ($fieldsByType): Component|Field {
            $name = $component->getStatePath(false);
            if ($fieldsByType === [] || $name === 'type') {
                return $component;
            }

            return $component->visible(fn (Get $get): bool => in_array($name, $fieldsByType[$get('type')] ?? [], true));
        }, $this->fields($template, $path, $source));
    }

    private function textField(string $name, string $label, string $original, bool $locked, bool $isLink = false): Field
    {
        $isLink = $isLink || $name === 'href' || str_ends_with($name, 'Href');
        $field = mb_strlen($original) > 90 || str_contains($original, "\n")
            ? Textarea::make($name)->rows(3)->autosize()
            : TextInput::make($name);

        // Locked values are not sent back: SiteContent keeps the original one.
        $field->label($label)->hiddenLabel($label === '')->disabled($locked)->dehydrated(! $locked);

        if ($isLink) {
            $field->maxLength(500)->helperText('Vacío = enlace automático. Acepta /ruta, #ancla, https://, mailto: o tel:.');
        }

        return $field;
    }

    /**
     * @param  array<int, string>  $patterns
     */
    private function matches(string $path, array $patterns): bool
    {
        return in_array($path, $patterns, true);
    }

    /**
     * Pairs ([a, b]) become ['c0' => a, 'c1' => b] so the repeater can name its fields.
     */
    private function toForm(mixed $value, mixed $default): mixed
    {
        if (! is_array($value)) {
            return $value;
        }

        if (array_is_list($value)) {
            $template = is_array($default) ? ($default[0] ?? null) : null;

            return array_map(function (mixed $item, int $index) use ($default, $template): mixed {
                if (is_array($item) && array_is_list($item)) {
                    return array_combine(array_map(fn (int $column): string => 'c'.$column, array_keys($item)), $item);
                }

                return $this->toForm($item, $default[$index] ?? $template);
            }, $value, array_keys($value));
        }

        foreach ($value as $name => $item) {
            $value[$name] = $this->toForm($item, $default[$name] ?? null);
        }

        return $value;
    }

    private function fromForm(mixed $value, mixed $default): mixed
    {
        if (! is_array($default)) {
            return is_scalar($value) ? (string) $value : '';
        }

        if (! is_array($value)) {
            return $default;
        }

        if (array_is_list($default)) {
            $template = $default[0] ?? '';

            return array_map(function (mixed $item, int $index) use ($default, $template): mixed {
                $itemDefault = $default[$index] ?? $template;
                if (is_array($itemDefault) && array_is_list($itemDefault)) {
                    return array_map(fn (int $column): string => (string) ($item['c'.$column] ?? ''), array_keys($itemDefault));
                }

                return $this->fromForm($item, $itemDefault);
            }, array_values($value), array_keys(array_values($value)));
        }

        $copy = [];
        foreach ($default as $name => $itemDefault) {
            $copy[$name] = $this->fromForm($value[$name] ?? null, $itemDefault);
        }

        return $copy;
    }
}
