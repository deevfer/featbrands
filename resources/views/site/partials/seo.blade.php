<title>{{ $seo['title'] }}</title>
<meta name="description" content="{{ $seo['description'] }}">
<meta data-site-meta name="robots" content="{{ $seo['robots'] }}">
<meta data-site-meta name="feat-site-id" content="{{ config('seo.site_id') }}">
<link data-site-meta rel="canonical" href="{{ $seo['canonical'] }}">
@foreach ($seo['alternates'] as $language => $href)
    <link data-site-meta rel="alternate" hreflang="{{ $language }}" href="{{ $href }}">
@endforeach
@if (isset($seo['alternates']['es']))<link data-site-meta rel="alternate" hreflang="x-default" href="{{ $seo['alternates']['es'] }}">@endif
<meta data-site-meta property="og:site_name" content="Feat.">
<meta data-site-meta property="og:type" content="{{ $seo['type'] }}">
<meta data-site-meta property="og:title" content="{{ $seo['og_title'] }}">
<meta data-site-meta property="og:description" content="{{ $seo['og_description'] }}">
<meta data-site-meta property="og:url" content="{{ $seo['canonical'] }}">
<meta data-site-meta property="og:locale" content="{{ $seo['locale'] === 'en' ? 'en_US' : 'es_GT' }}">
<meta data-site-meta property="og:image" content="{{ $seo['image'] }}">
<meta data-site-meta property="og:image:alt" content="{{ $seo['image_alt'] }}">
<meta data-site-meta name="twitter:card" content="summary_large_image">
<meta data-site-meta name="twitter:title" content="{{ $seo['title'] }}">
<meta data-site-meta name="twitter:description" content="{{ $seo['description'] }}">
<meta data-site-meta name="twitter:image" content="{{ $seo['image'] }}">
@if ($seo['published'])<meta data-site-meta property="article:published_time" content="{{ $seo['published'] }}">@endif
@if ($seo['modified'])<meta data-site-meta property="article:modified_time" content="{{ $seo['modified'] }}">@endif
@if ($seo['verification']['search_console_verification'])<meta data-site-meta name="google-site-verification" content="{{ $seo['verification']['search_console_verification'] }}">@endif
@if ($seo['verification']['bing_verification'])<meta data-site-meta name="msvalidate.01" content="{{ $seo['verification']['bing_verification'] }}">@endif
<script data-site-meta type="application/ld+json">{!! json_encode($seo['schema'], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}</script>
