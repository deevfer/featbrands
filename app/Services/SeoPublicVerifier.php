<?php

namespace App\Services;

use App\Models\SeoSetting;
use Illuminate\Support\Facades\Http;
use Throwable;

class SeoPublicVerifier
{
    /** @return array<int, string> */
    public function addresses(string $host): array
    {
        if (filter_var($host, FILTER_VALIDATE_IP)) {
            $addresses = [$host];
        } else {
            if (! function_exists('dns_get_record')) {
                return [];
            }
            $records = dns_get_record($host, DNS_A | DNS_AAAA);
            $addresses = array_values(array_filter(array_map(fn (array $record): ?string => $record['ip'] ?? $record['ipv6'] ?? null, $records ?: [])));
        }
        foreach ($addresses as $address) {
            if (! filter_var($address, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                return [];
            }
        }

        return $addresses;
    }

    /** @return array<string, mixed> */
    public function verify(): array
    {
        $seo = app(SiteSeo::class);
        $origin = $seo->origin();
        $host = parse_url($origin, PHP_URL_HOST);
        $report = ['origin' => $origin, 'checked_at' => now()->toIso8601String(), 'passed' => false, 'checks' => []];
        if (! extension_loaded('curl') || ! $host || parse_url($origin, PHP_URL_SCHEME) !== 'https' || ! ($addresses = $this->addresses($host))) {
            $report['checks'][] = ['label' => 'Dominio público', 'passed' => false, 'detail' => 'Se requiere un dominio HTTPS que resuelva únicamente a direcciones públicas.'];
        } else {
            $resolved = str_contains($addresses[0], ':') ? '['.$addresses[0].']' : $addresses[0];
            $client = Http::connectTimeout(2)->timeout(4)->withoutRedirecting()->withOptions(['curl' => [CURLOPT_RESOLVE => [$host.':443:'.$resolved]]]);
            foreach (['Googlebot', 'OAI-SearchBot', 'PerplexityBot'] as $agent) {
                try {
                    $response = (clone $client)->withUserAgent($agent)->get($origin.'/');
                    $body = $response->body();
                    $identity = str_contains($body, 'content="'.config('seo.site_id').'"');
                    $robots = preg_match('/<meta\b[^>]*name="robots"[^>]*content="([^\"]*)"/i', $body, $matches) ? $matches[1] : '';
                    $passed = $response->ok() && $identity && str_contains($robots, 'index,') && ! str_contains($robots, 'noindex') && ! str_contains(strtolower($response->header('X-Robots-Tag') ?? ''), 'noindex');
                    $report['checks'][] = ['label' => $agent, 'passed' => $passed, 'detail' => 'HTTP '.$response->status().'. '.($identity ? ($passed ? 'El nuevo sitio responde y permite indexación.' : 'El nuevo sitio todavía indica noindex.') : 'No se ha identificado esta versión del sitio en el dominio.')];
                } catch (Throwable) {
                    $report['checks'][] = ['label' => $agent, 'passed' => false, 'detail' => 'No se pudo comprobar el acceso HTTPS. Revisa DNS, certificado y reglas de seguridad de HostGator.'];
                }
            }
            foreach (['/robots.txt', '/sitemap.xml'] as $path) {
                try {
                    $response = (clone $client)->get($origin.$path);
                    $body = $response->body();
                    $passed = $response->ok() && ($path === '/robots.txt' ? str_contains($body, 'OAI-SearchBot') && str_contains($body, 'PerplexityBot') && ! preg_match('~Disallow:\s*/\s*(?:\r?\n|$)~i', $body) : str_contains($body, '<sitemapindex') && str_contains($body, $origin.'/blog-posts-sitemap.xml'));
                    $report['checks'][] = ['label' => $path, 'passed' => (bool) $passed, 'detail' => 'HTTP '.$response->status().'. '.($passed ? 'Disponible y consistente con la configuración.' : 'La respuesta no coincide con la configuración esperada.')];
                } catch (Throwable) {
                    $report['checks'][] = ['label' => $path, 'passed' => false, 'detail' => 'No se pudo obtener el archivo desde el dominio público.'];
                }
            }
            $report['passed'] = collect($report['checks'])->every('passed');
        }
        $settings = SeoSetting::firstOrNew(['id' => 1]);
        $settings->data = [...($settings->data ?? []), 'public_verification' => $report];
        $settings->save();

        return $report;
    }
}
