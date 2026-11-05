<?php

namespace App\Services;

use App\Http\Controllers\BlogController;
use App\Http\Controllers\SeoController;
use App\Http\Controllers\SiteController;
use App\Http\Middleware\SeoHeaders;
use App\Models\BlogPost;
use App\Models\SeoAudit;
use DOMDocument;
use DOMXPath;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ViewErrorBag;
use Throwable;

class SeoAuditor
{
    public function __construct(private SiteSeo $seo) {}

    public function run(): SeoAudit
    {
        $pages = [];
        foreach (['es', 'en'] as $locale) {
            foreach (SiteSeo::PAGES as $page) {
                $pages[] = $this->inspect($this->seo->mainPath($page, $locale), $locale, $page);
            }
        }
        foreach (BlogPost::published()->orderBy('id')->cursor() as $post) {
            $pages[] = $this->inspect($post->path, $post->locale, 'post', $post);
        }
        $counts = array_count_values(array_map(fn (array $page): string => $page['locale'].'|'.$page['title'], $pages));
        foreach ($pages as &$page) {
            $unique = $page['checks'][0]['passed'] && $counts[$page['locale'].'|'.$page['title']] === 1;
            $page['checks'][] = $this->check('unique', 'Título único en su idioma', 5, $unique, 'Evita repetir el mismo título SEO entre páginas del mismo idioma.');
            $page['score'] = max(1, array_sum(array_column($page['checks'], 'earned')));
        }
        unset($page);
        $global = $this->globalChecks();
        $site = array_sum(array_column($global, 'earned'));
        $corporate = collect($pages)->where('kind', 'page')->avg('score') ?? 0;
        $articles = collect($pages)->where('kind', 'post')->avg('score');
        $score = max(1, (int) round($site * .4 + $corporate * ($articles === null ? .6 : .35) + ($articles ?? 0) * .25));
        $issues = [];
        if ($score === 100 && ($site < 100 || collect($pages)->contains(fn (array $page): bool => $page['score'] < 100))) {
            $score = 99;
        }
        foreach ($pages as $page) {
            foreach ($page['checks'] as $check) {
                if (! $check['passed']) {
                    $issues[$check['key']] ??= ['label' => $check['label'], 'help' => $check['help'], 'count' => 0, 'weight' => $check['weight']];
                    $issues[$check['key']]['count']++;
                }
            }
        }
        usort($pages, fn (array $a, array $b): int => $a['score'] <=> $b['score']);
        uasort($issues, fn (array $a, array $b): int => ($b['weight'] * $b['count']) <=> ($a['weight'] * $a['count']));
        $report = ['version' => 1, 'score' => $score, 'site_score' => $site, 'pages_score' => (int) floor($corporate), 'articles_score' => $articles === null ? null : (int) floor($articles), 'checked_at' => now()->toIso8601String(), 'origin' => $this->seo->origin(), 'environment' => app()->environment(), 'global' => $global, 'pages' => $pages, 'issues' => array_values($issues), 'public_verification' => $this->seo->settings()['public_verification'] ?? null];
        $audit = SeoAudit::create(['score' => $score, 'page_count' => count($pages), 'report' => $report]);
        SeoAudit::whereNotIn('id', SeoAudit::latest('id')->limit(20)->pluck('id'))->delete();

        return $audit;
    }

    /** @return array<string, mixed> */
    private function inspect(string $path, string $locale, string $page, ?BlogPost $post = null): array
    {
        $entry = ['path' => $path, 'locale' => $locale, 'kind' => $post ? 'post' : 'page', 'title' => $post?->title ?? ucfirst($page), 'score' => 1, 'edit_url' => $post ? '/admin/blog-posts/'.$post->id.'/edit' : '/admin/seo-pages', 'checks' => []];
        try {
            $request = Request::create($this->seo->absolute($path));
            $route = Route::getRoutes()->match($request);
            $route->bind($request);
            $request->setRouteResolver(fn () => $route);
            $site = app(SiteController::class);
            $view = $post ? app(BlogController::class)->post($request, $site, app(BlogContent::class), $post->slug) : ($page === 'blog' ? app(BlogController::class)->index($request, $site) : $site->render($request, $page));
            $html = $view->with('errors', new ViewErrorBag)->render();
            $document = new DOMDocument;
            $previous = libxml_use_internal_errors(true);
            $document->loadHTML('<?xml encoding="UTF-8">'.$html, LIBXML_NONET);
            libxml_clear_errors();
            libxml_use_internal_errors($previous);
            $xpath = new DOMXPath($document);
            $title = trim($xpath->evaluate('string(//title)'));
            $entry['title'] = $title;
            $description = $xpath->evaluate('string(//meta[@name="description"]/@content)');
            $canonical = $xpath->evaluate('string(//link[@rel="canonical"]/@href)');
            $text = trim(preg_replace('/\s+/u', ' ', $xpath->evaluate('string(//main)')));
            $schemas = [];
            foreach ($xpath->query('//script[@type="application/ld+json"]') as $script) {
                $schema = json_decode($script->textContent, true);
                foreach ($schema['@graph'] ?? [$schema] as $node) {
                    if (isset($node['@type'])) {
                        $schemas[] = $node['@type'];
                    }
                }
            }
            $missingImages = 0;
            foreach ($xpath->query('//main//img') as $image) {
                if (! $image->hasAttribute('alt') || ! $image->getAttribute('src')) {
                    $missingImages++;

                    continue;
                }
                $src = $image->getAttribute('src');
                if (str_starts_with($src, '/') || str_starts_with($src, $this->seo->origin().'/') || str_starts_with($src, rtrim(config('app.url'), '/').'/')) {
                    $imagePath = rawurldecode(parse_url($src, PHP_URL_PATH) ?? '');
                    if (! is_file(public_path(ltrim($imagePath, '/')))) {
                        $missingImages++;
                    }
                }
            }
            $sharingImage = $xpath->evaluate('string(//meta[@property="og:image"]/@content)');
            $sharingReady = (bool) $sharingImage && (bool) $xpath->evaluate('string(//meta[@name="twitter:card"]/@content)');
            if (str_starts_with($sharingImage, $this->seo->origin().'/')) {
                $sharingReady = $sharingReady && is_file(public_path(ltrim(rawurldecode(parse_url($sharingImage, PHP_URL_PATH)), '/')));
            }
            $selfLanguage = false;
            foreach ($xpath->query('//link[@rel="alternate"][@hreflang]') as $link) {
                if ($link->getAttribute('hreflang') === $locale && $link->getAttribute('href') === $canonical) {
                    $selfLanguage = true;
                }
            }
            $entry['checks'] = [
                $this->check('render', 'HTML generado sin errores', 10, true, 'Revisa la ruta y los errores del servidor si la página no se puede generar.'),
                $this->check('title', 'Título SEO descriptivo', 10, mb_strlen($title) >= 15 && mb_strlen($title) <= 70, 'Usa un título claro. Este panel sugiere 15–70 caracteres; es un criterio editorial, no un límite de Google.'),
                $this->check('description', 'Descripción SEO útil', 10, mb_strlen($description) >= 60 && mb_strlen($description) <= 180, 'Resume el contenido real. Este panel sugiere 60–180 caracteres, sin garantizar cómo lo mostrará el buscador.'),
                $this->check('canonical', 'URL canónica consistente', 10, $canonical === $this->seo->absolute($path) && $xpath->query('//link[@rel="canonical"]')->length === 1, 'Publica una sola URL canónica por página y excluye parámetros de mercado y seguimiento.'),
                $this->check('heading', 'Un título principal H1', 10, $xpath->query('//main//h1')->length === 1, 'La página debe tener un único encabezado principal que describa su contenido.'),
                $this->check('html', 'Contenido disponible en HTML', 10, mb_strlen($text) > 80, 'El contenido principal debe poder leerse sin ejecutar JavaScript.'),
                $this->check('schema', 'Datos estructurados coherentes', 10, in_array('Organization', $schemas, true) && in_array($post ? 'BlogPosting' : (in_array($page, ['performance', 'brand', 'social', 'experience', 'makitha'], true) ? 'Service' : ($page === 'blog' ? 'CollectionPage' : 'WebPage')), $schemas, true), 'Mantén datos estructurados válidos que describan información visible y comprobable.'),
                $this->check('sharing', 'Tarjeta para compartir', 10, $sharingReady, 'Añade una imagen y metadatos para compartir la página.'),
                $this->check('images', 'Imágenes locales y atributos alt', 5, $missingImages === 0, 'Corrige imágenes locales que no existen y añade alt descriptivo, o alt vacío si son decorativas.'),
                $this->check('links', 'Navegación o contacto accesibles', 5, ($xpath->query('//main//a[@href and not(starts-with(@href,"#"))]')->length > 0 || ($page === 'contact' && $xpath->query('//main//form[@action]')->length > 0)), 'Conecta la página con contenido relevante o, en Contacto, proporciona un formulario accesible.'),
                $this->check('language', 'Idioma y alternativa canónica', 5, $xpath->evaluate('string(//html/@lang)') === $locale && $selfLanguage, 'Declara el idioma y sus traducciones reales mediante hreflang.'),
            ];
        } catch (Throwable $error) {
            report($error);
            $entry['checks'] = [$this->check('render', 'La página no se pudo generar', 95, false, 'Revisa los registros de Laravel y vuelve a analizar. Las comprobaciones de esta página quedan sin aprobar.')];
        }

        return $entry;
    }

    /** @return array<int, array<string, mixed>> */
    private function globalChecks(): array
    {
        $robots = app(SeoController::class)->robots($this->seo)->getContent();
        $request = Request::create('/site-pages-sitemap.xml');
        $route = Route::getRoutes()->match($request);
        $route->bind($request);
        $request->setRouteResolver(fn () => $route);
        $sitemap = app(SeoController::class)->sitemap($request, $this->seo)->getContent();
        $document = new DOMDocument;
        $xmlValid = $document->loadXML($sitemap, LIBXML_NONET);
        $private = (new SeoHeaders)->handle(Request::create('/admin/login'), fn () => response(''))->headers->get('X-Robots-Tag');
        $runtime = PHP_VERSION_ID >= 80401 && collect(['curl', 'dom', 'mbstring', 'pdo', 'xmlwriter', 'fileinfo'])->every(fn (string $extension): bool => extension_loaded($extension));
        $verification = $this->seo->settings()['public_verification'] ?? null;
        $verified = $verification && ($verification['passed'] ?? false) && $verification['origin'] === $this->seo->origin() && now()->subDays(7)->lt($verification['checked_at']);
        $htaccess = file_get_contents(public_path('.htaccess'));

        return [
            $this->check('indexing', 'Indexación pública habilitada', 10, $this->seo->publicIndexingEnabled(), 'En HostGator usa APP_ENV=production y habilita la indexación en Configuración SEO. En local se mantiene noindex.'),
            $this->check('crawlers', 'Acceso para buscadores de IA', 10, str_contains($robots, 'OAI-SearchBot') && str_contains($robots, 'PerplexityBot') && str_contains($robots, 'Googlebot'), 'robots.txt permite los rastreadores de búsqueda cuando el sitio está en producción. El firewall se verifica después de publicar.'),
            $this->check('sitemap', 'Sitemap XML automático', 15, $xmlValid && $document->getElementsByTagName('loc')->length >= 20, 'El sitemap debe incluir las páginas e idiomas y actualizarse con los artículos publicados.'),
            $this->check('domain', 'Dominio previsto con HTTPS', 10, str_starts_with($this->seo->origin(), 'https://') && ! in_array(parse_url($this->seo->origin(), PHP_URL_HOST), ['localhost', '127.0.0.1'], true), 'Confirma el dominio HTTPS en Configuración SEO. Este chequeo valida la configuración, no el certificado público.'),
            $this->check('private', 'CMS excluido de indexación', 10, $private === 'noindex, nofollow', 'El panel administrativo y Livewire deben permanecer fuera de los índices.'),
            $this->check('runtime', 'PHP y extensiones compatibles', 10, $runtime, 'El composer.lock actual requiere PHP 8.4.1 o superior. En HostGator verifica PHP del dominio y de la terminal.'),
            $this->check('production', 'Entorno preparado para producción', 10, app()->environment('production') && ! config('app.debug'), 'Publica con APP_ENV=production y APP_DEBUG=false. Esta comprobación corresponde al servidor donde se ejecuta el panel.'),
            $this->check('apache', 'Configuración Apache preparada', 10, str_contains($htaccess, 'RewriteRule ^ index.php') && str_contains($htaccess, 'mod_expires.c') && str_contains($htaccess, 'mod_deflate.c'), 'Se incluyen reglas de rutas, compresión y caché de archivos estáticos para Apache. La activación de módulos se confirma en HostGator.'),
            $this->check('public', 'Publicación comprobada desde el servidor', 15, (bool) $verified, 'Ejecuta Verificar publicación tras desplegar. Se comprueban HTTPS, la identidad de esta versión, noindex, robots.txt y sitemap. La verificación vence a los 7 días.'),
        ];
    }

    /** @return array<string, mixed> */
    private function check(string $key, string $label, int $weight, bool $passed, string $help): array
    {
        return ['key' => $key, 'label' => $label, 'weight' => $weight, 'earned' => $passed ? $weight : 0, 'passed' => $passed, 'help' => $help];
    }
}
