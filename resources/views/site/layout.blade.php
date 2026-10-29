
<!DOCTYPE html>
<html lang="{{ $locale }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="{{ $pageDescription }}">
    <title>{{ $pageTitle }}</title>

    {{-- INICIO: SEO dinámico de Home --}}
    @if (in_array($page, ['home', 'performance'], true) && !empty($pageSeo))
        <meta name="robots" content="{{ $pageSeo['robots'] }}">

        <link rel="canonical" href="{{ $pageSeo['canonical'] }}">

        <link rel="alternate" hreflang="es" href="{{ $pageSeo['alternates']['es'] }}">
        <link rel="alternate" hreflang="en" href="{{ $pageSeo['alternates']['en'] }}">
        <link rel="alternate" hreflang="x-default" href="{{ $pageSeo['alternates']['es'] }}">

        {{-- LinkedIn y redes sociales: Open Graph --}}
        <meta property="og:type" content="website">
        <meta property="og:site_name" content="Feat.">
        <meta property="og:locale" content="{{ $pageSeo['og_locale'] }}">
        <meta property="og:url" content="{{ $pageSeo['canonical'] }}">
        <meta property="og:title" content="{{ $pageSeo['og_title'] }}">
        <meta property="og:description" content="{{ $pageSeo['og_description'] }}">

        @if (!empty($pageSeo['og_image']))
            <meta property="og:image" content="{{ $pageSeo['og_image'] }}">

            @if (!empty($pageSeo['og_image_alt']))
                <meta property="og:image:alt" content="{{ $pageSeo['og_image_alt'] }}">
            @endif
        @endif
    @endif
    {{-- FIN: SEO dinámico de Home --}}

    <link rel="icon" href="{{ asset('assets/feat-black.svg') }}" type="image/svg+xml">
    <link rel="stylesheet" href="{{ asset('css/site.css') }}">
    <script src="{{ asset('js/site.js') }}" defer></script>
    <script src="{{ asset('js/language.js') }}" defer></script>
    @if ($copy !== null)
        <link rel="stylesheet" href="{{ asset('css/specializations.css') }}">
        <script src="{{ asset('js/specializations.js') }}" defer></script>
    @endif
    @if ($page === 'contact')
        <link rel="stylesheet" href="{{ asset('css/contact.css') }}">
        <script src="{{ asset('js/contact.js') }}" defer></script>
    @endif
    @if (in_array($page, ['cases', 'about'], true))
        <link rel="stylesheet" href="{{ asset('css/agency.css') }}">
        <script src="{{ asset('js/agency.js') }}" defer></script>
    @endif
</head>
<body data-page="{{ $page }}" data-market="{{ $market }}" data-performance-url="{{ $performanceUrl }}">
    <a class="skip-link" href="#main-content">{{ $locale === 'es' ? 'Ir al contenido' : 'Skip to content' }}</a>
    <div class="site-shell" style="font-family:'Space Grotesk',sans-serif;background:#FFFFFF;color:#000000;min-height:100vh;overflow-x:clip">
        @include('site.partials.header')
        @yield('content')
        @include('site.partials.footer')
    </div>
    <p class="site-language-status" data-language-status role="status" hidden></p>
</body>
</html>
