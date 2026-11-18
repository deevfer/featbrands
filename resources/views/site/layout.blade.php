
<!DOCTYPE html>
<html lang="{{ $locale }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @include('site.partials.seo')
    @include('site.partials.measurement-head', ['siteSettings' => $siteSettings = \App\Models\SiteSetting::current()])
    @stack('head')
    <link rel="icon" href="{{ $brandAssets['favicon'] }}">
    <link rel="stylesheet" href="{{ asset('css/site.css').'?v='.filemtime(public_path('css/site.css')) }}">
    <script src="{{ asset('js/site.js').'?v='.filemtime(public_path('js/site.js')) }}" defer></script>
    <script src="{{ asset('js/language.js').'?v='.filemtime(public_path('js/language.js')) }}" defer></script>
    @if ($copy !== null)
        <link rel="stylesheet" href="{{ asset('css/specializations.css').'?v='.filemtime(public_path('css/specializations.css')) }}">
        <script src="{{ asset('js/specializations.js').'?v='.filemtime(public_path('js/specializations.js')) }}" defer></script>
    @endif
    @if ($page === 'contact')
        <link rel="stylesheet" href="{{ asset('css/contact.css').'?v='.filemtime(public_path('css/contact.css')) }}">
        <script src="{{ asset('js/contact.js').'?v='.filemtime(public_path('js/contact.js')) }}" defer></script>
    @endif
    @if ($page === 'blog')
        <link rel="stylesheet" href="{{ asset('css/blog.css').'?v='.filemtime(public_path('css/blog.css')) }}">
        <script src="{{ asset('js/blog.js').'?v='.filemtime(public_path('js/blog.js')) }}" defer></script>
    @endif
    @if (in_array($page, ['cases', 'about'], true))
        <link rel="stylesheet" href="{{ asset('css/agency.css').'?v='.filemtime(public_path('css/agency.css')) }}">
        <script src="{{ asset('js/agency.js').'?v='.filemtime(public_path('js/agency.js')) }}" defer></script>
    @endif
</head>
<body data-page="{{ $page }}" data-market="{{ $market }}" data-performance-url="{{ $performanceUrl }}">
    <a class="skip-link" href="#main-content">{{ $t['skipLink'] }}</a>
    <div class="site-shell" style="font-family:'Space Grotesk',sans-serif;background:#FFFFFF;color:#000000;min-height:100vh;overflow-x:clip">
        @include('site.partials.header')
        @yield('content')
        @include('site.partials.footer')
    </div>
    <p class="site-language-status" data-language-status role="status" hidden></p>
    @include('site.partials.measurement-body', ['siteSettings' => $siteSettings])
</body>
</html>
