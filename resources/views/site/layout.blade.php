<!DOCTYPE html>
<html lang="{{ $locale }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="{{ $pageDescription }}">
    <title>{{ $pageTitle }}</title>
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
