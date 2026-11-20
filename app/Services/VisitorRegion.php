<?php

namespace App\Services;

use Illuminate\Http\Request;

/**
 * Starting language and market for a first visit, from the visitor's country.
 * Guatemala, Costa Rica and El Salvador start in Spanish with their market, the United States in English,
 * and any other country in Spanish with Guatemala. The visitor can still switch both from the header.
 */
class VisitorRegion
{
    public const COOKIE = 'feat_region';

    public const COOKIE_MINUTES = 60 * 24 * 365;

    /** @var array{lang: string, market: string} */
    public const DEFAULT = ['lang' => 'es', 'market' => 'gt'];

    /** @var array<string, array{lang: string, market: string}> */
    public const BY_COUNTRY = [
        'GT' => ['lang' => 'es', 'market' => 'gt'],
        'CR' => ['lang' => 'es', 'market' => 'cr'],
        'SV' => ['lang' => 'es', 'market' => 'sv'],
        'US' => ['lang' => 'en', 'market' => 'us'],
    ];

    private const CRAWLERS = '/bot|crawl|spider|slurp|mediapartners|facebookexternalhit|embedly|preview|lighthouse|pagespeed|headless|monitor|curl|wget|python/i';

    public function __construct(private IpCountryLookup $lookup) {}

    /**
     * Country from Cloudflare when the site is behind it, otherwise from the local IP ranges.
     */
    public function country(Request $request): ?string
    {
        $header = strtoupper(trim((string) $request->header('CF-IPCountry')));

        if (preg_match('/^[A-Z]{2}$/', $header) === 1 && $header !== 'XX') {
            return $header;
        }

        return $this->lookup->country($request->ip());
    }

    /**
     * @return array{lang: string, market: string}
     */
    public function forCountry(?string $country): array
    {
        return self::BY_COUNTRY[$country] ?? self::DEFAULT;
    }

    /**
     * Same fallbacks the pages use for the lang and market query parameters.
     *
     * @return array{lang: string, market: string}
     */
    public function normalize(mixed $lang, mixed $market): array
    {
        return [
            'lang' => $lang === 'en' ? 'en' : 'es',
            'market' => in_array($market, ['gt', 'cr', 'sv', 'us'], true) ? $market : 'gt',
        ];
    }

    /**
     * @return array{lang: string, market: string}|null
     */
    public function fromCookie(mixed $value): ?array
    {
        if (! is_string($value) || preg_match('/^(es|en)\|(gt|cr|sv|us)$/', $value, $parts) !== 1) {
            return null;
        }

        return ['lang' => $parts[1], 'market' => $parts[2]];
    }

    /**
     * @param  array{lang: string, market: string}  $choice
     */
    public function cookieValue(array $choice): string
    {
        return $choice['lang'].'|'.$choice['market'];
    }

    public function isCrawler(Request $request): bool
    {
        $agent = (string) $request->userAgent();

        return $agent === '' || preg_match(self::CRAWLERS, $agent) === 1;
    }
}
