<?php

namespace App\Http\Middleware;

use App\Services\VisitorRegion;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Opens a page without lang or market in its URL in the visitor's language and market:
 * the choice saved in the cookie first, otherwise the one for the visitor's country.
 * Links with lang or market always win and become the saved choice, so the header toggles keep working.
 * Crawlers are never redirected, so every language keeps its own indexable URL.
 */
class DetectVisitorRegion
{
    public function __construct(private VisitorRegion $region) {}

    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->isMethod('GET') || $this->region->isCrawler($request)) {
            return $next($request);
        }

        if ($request->query->has('lang') || $request->query->has('market')) {
            return $this->remember($next($request), $this->region->normalize($request->query('lang'), $request->query('market')));
        }

        $choice = $this->region->fromCookie($request->cookie(VisitorRegion::COOKIE))
            ?? $this->region->forCountry($this->region->country($request));

        if ($choice === VisitorRegion::DEFAULT) {
            return $this->remember($next($request), $choice);
        }

        $redirect = redirect()->to($request->url().'?'.http_build_query([...$request->query(), ...$choice]));
        $redirect->headers->set('Cache-Control', 'private, no-store');

        return $this->remember($redirect, $choice);
    }

    /**
     * @param  array{lang: string, market: string}  $choice
     */
    private function remember(Response $response, array $choice): Response
    {
        $response->headers->setCookie(cookie(VisitorRegion::COOKIE, $this->region->cookieValue($choice), VisitorRegion::COOKIE_MINUTES));

        return $response;
    }
}
