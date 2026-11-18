<?php

namespace App\Services;

use App\Models\SiteSetting;

/**
 * Measurement tags saved in the CMS, loaded only after the visitor's cookie consent.
 */
class MeasurementScripts
{
    /**
     * Valid IDs only; a legacy Meta Pixel pasted in the custom scripts is reused.
     *
     * @return array{measurementId: string|null, tagManagerId: string|null, adsId: string|null, pixelId: string|null}
     */
    public function configuration(SiteSetting $site): array
    {
        preg_match('~fbq\(\s*[\'"]init[\'"]\s*,\s*[\'"]([0-9]+)[\'"]~', $site->custom_head_scripts ?? '', $legacyPixel);

        return [
            'measurementId' => preg_match('/^G-[A-Z0-9]+$/', $site->google_analytics_id ?? '') ? $site->google_analytics_id : null,
            'tagManagerId' => preg_match('/^GTM-[A-Z0-9]+$/', $site->google_tag_manager_id ?? '') ? $site->google_tag_manager_id : null,
            'adsId' => preg_match('/^AW-[0-9]+$/', $site->google_ads_id ?? '') ? $site->google_ads_id : null,
            'pixelId' => preg_match('/^[0-9]+$/', $site->meta_pixel_id ?? '') ? $site->meta_pixel_id : ($legacyPixel[1] ?? null),
        ];
    }

    /**
     * Removes only standard copies of tags already managed through the CMS ID fields;
     * custom event code and unrelated tags are kept.
     */
    public function withoutDuplicates(?string $html, SiteSetting $site): string
    {
        $config = $this->configuration($site);
        $managed = array_filter([$config['measurementId'], $config['adsId']]);
        $html = preg_replace_callback('~<script\b([^>]*)>(.*?)</script>~is', function (array $match) use ($managed): string {
            if (preg_match('~\bsrc\s*=\s*[\'"]([^\'"]+)[\'"]~i', $match[1], $src)) {
                $url = parse_url(html_entity_decode($src[1]));
                parse_str($url['query'] ?? '', $query);

                return ($url['host'] ?? '') === 'www.googletagmanager.com' && ($url['path'] ?? '') === '/gtag/js'
                    && in_array($query['id'] ?? null, $managed, true) ? '' : $match[0];
            }
            $code = $match[2];
            foreach ($managed as $id) {
                $code = preg_replace('~gtag\(\s*([\'"])config\1\s*,\s*([\'"])'.preg_quote($id, '~').'\2\s*\)\s*;?~', '', $code);
            }
            if ($code === $match[2]) {
                return $match[0];
            }
            $code = preg_replace([
                '~window\.dataLayer\s*=\s*window\.dataLayer\s*\|\|\s*\[\]\s*;?~',
                '~function\s+gtag\s*\(\s*\)\s*\{\s*dataLayer\.push\(arguments\)\s*;?\s*\}\s*;?~',
                '~gtag\(\s*([\'"])js\1\s*,\s*new\s+Date\(\s*\)\s*\)\s*;?~',
            ], '', $code);

            return trim($code) === '' ? '' : '<script'.$match[1].'>'.$code.'</script>';
        }, $html ?? '');
        if ($config['tagManagerId']) {
            $html = preg_replace('~<noscript>\s*<iframe\b[^>]*src=[\'"]https://www\.googletagmanager\.com/ns\.html\?id='.preg_quote($config['tagManagerId'], '~').'[\'"][^>]*>\s*</iframe>\s*</noscript>~is', '', $html);
        }

        if ($config['pixelId']) {
            $html = preg_replace_callback('~<script\b[^>]*>(.*?)</script>~is', fn ($match) => str_contains($match[1], 'fbevents.js') && preg_match('~fbq\(\s*[\'"]init[\'"]\s*,\s*[\'"]'.preg_quote($config['pixelId'], '~').'[\'"]~', $match[1]) ? '' : $match[0], $html);
        }

        return $html;
    }

    /**
     * Turns every script into an inert template that the consent banner activates.
     */
    public function withConsent(?string $html, string $category = 'marketing'): string
    {
        $html = preg_replace('~<noscript\b[^>]*>.*?</noscript>~is', '', $html ?? '');

        return preg_replace_callback('~<script\b([^>]*)>(.*?)</script>~is', function ($match) use ($category): string {
            $attributes = preg_replace('~\s+type\s*=\s*([\'"]).*?\1~is', '', $match[1]);
            $attributes = preg_replace('~\bsrc\s*=~i', 'data-src=', $attributes);

            return '<script type="text/plain" data-feat-consent="'.$category.'"'.$attributes.'>'.$match[2].'</script>';
        }, $html);
    }
}
