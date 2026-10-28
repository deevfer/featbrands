<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

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
        $market = in_array($request->query('market'), ['gt', 'cr', 'us'], true) ? $request->query('market') : 'gt';
        $query = ['lang' => $locale, 'market' => $market];
        $t = File::json(resource_path('data/site.json'))[$locale];
        $homeUrl = route('home', $query);
        $performanceUrl = route('performance', $query);
        $serviceRoutes = ['performance', 'brand', 'experience', 'makitha', 'social'];
        $serviceUrls = array_combine($serviceRoutes, array_map(fn (string $route): string => route($route, $query), $serviceRoutes));
        $copy = match ($page) {
            'home', 'performance' => null,
            'cases', 'about' => File::json(resource_path('data/agency.json'))[$locale][$page],
            'contact' => File::json(resource_path('data/contact.json'))[$locale],
            default => File::json(resource_path('data/specializations.json'))[$locale][$page],
        };
        $agencyUrls = [
            'cases' => route('cases', $query),
            'about' => route('about', $query),
            'awards' => route('about', $query).'#reconocimientos',
        ];
        $contactUrl = route('contact', $query);
        $pageTitle = match ($page) {
            'home' => 'Feat. — Collab Driven Growth',
            'performance' => 'Performance & Connections — Feat.',
            default => $copy['name'].' — Feat.',
        };
        $pageDescription = match ($page) {
            'home' => $t['hero']['sub'],
            'performance' => $t['perf']['answer'],
            default => $copy['intro'],
        };
        $colors = [['#FB6C26', '#000000'], ['#2D48F2', '#FFFFFF'], ['#15F7A8', '#000000'], ['#FDDB02', '#000000'], ['#D48BFD', '#000000']];
        $serviceLinks = [];
        $deckCards = [];

        foreach ($t['services'] as $index => $service) {
            $serviceLinks[] = [
                'title' => $service['title'],
                'href' => $serviceUrls[$serviceRoutes[$index]],
                'route' => $serviceRoutes[$index],
                'color' => ['#2D48F2', '#FB6C26', '#15F7A8', '#FDDB02', '#D48BFD'][$index],
            ];
            $deckCards[] = [
                ...$service,
                'num' => sprintf('%02d', $index + 1),
                'background' => $colors[$index][0],
                'foreground' => $colors[$index][1],
                'cta' => $t['servicesBtn'],
                'href' => $serviceUrls[$serviceRoutes[$index]],
            ];
        }

        $navRest = [
            ['label' => $t['nav'][1], 'href' => $agencyUrls['cases'], 'route' => 'cases'],
            ['label' => $t['nav'][2], 'href' => $agencyUrls['about'], 'route' => 'about'],
            ['label' => $t['nav'][3], 'href' => $homeUrl, 'route' => null],
            ['label' => $t['nav'][4], 'href' => $contactUrl, 'route' => 'contact'],
        ];

        return view('site.'.$page, [
            't' => $t,
            'p' => $t['perf'],
            'page' => $page,
            'copy' => $copy,
            'pageTitle' => $pageTitle,
            'pageDescription' => $pageDescription,
            'serviceUrls' => $serviceUrls,
            'agencyUrls' => $agencyUrls,
            'footerAgencyUrls' => [$agencyUrls['about'], $agencyUrls['cases'], $agencyUrls['awards'], $homeUrl],
            'locale' => $locale,
            'market' => $market,
            'homeUrl' => $homeUrl,
            'contactUrl' => $contactUrl,
            'phoneCountries' => $page === 'contact'
                ? collect(File::json(resource_path('data/calling-codes.json'))['countries'])->sortBy('name.'.$locale, SORT_NATURAL | SORT_FLAG_CASE)->values()->all()
                : [],
            'performanceUrl' => $performanceUrl,
            'languageUrls' => [
                'es' => route($request->route()->getName(), ['lang' => 'es', 'market' => $market]),
                'en' => route($request->route()->getName(), ['lang' => 'en', 'market' => $market]),
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
}
