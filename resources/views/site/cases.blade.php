@extends('site.layout')

@section('content')
<main id="main-content" class="specialization cases-page">
    <section class="cases-opening">
        <div class="service-width">
            <div class="cases-masthead"><p class="service-eyebrow">{{ $copy['kicker'] }}</p><span>feat. / {{ $copy['name'] }}</span></div>
            <div class="cases-cover"><h1>{{ $copy['title'] }}<em>{{ $copy['accent'] }}</em></h1><div class="cases-cover-note"><span class="cases-cover-arrow" aria-hidden="true">↙</span><p>{{ $copy['intro'] }}</p><a href="{{ $agencyUrls['about'] }}">{{ $copy['aboutLink'] }} ↗</a></div></div>
            <div class="cases-cover-bottom"><span>{{ $copy['heroNote'] }}</span><span>{{ sprintf('%02d', count($copy['studies'])) }} / {{ $copy['countPlural'] }}</span></div>
        </div>
    </section>

    <section class="cases-archive service-width" data-case-archive data-filter="all">
        <div class="cases-archive-heading"><div><p class="service-eyebrow">{{ $copy['name'] }} / {{ $locale === 'es' ? 'Una mirada al trabajo' : 'A look at the work' }}</p><h2>{{ $copy['archiveTitle'] }}</h2></div><p class="service-body">{{ $copy['archiveIntro'] }}</p></div>
        <div class="cases-toolbar"><div class="case-filters" role="group" aria-label="{{ $locale === 'es' ? 'Filtrar casos por enfoque' : 'Filter work by focus' }}">@foreach ($copy['filters'] as $filter)<button type="button" data-case-filter="{{ $filter[0] }}" aria-pressed="{{ $loop->first ? 'true' : 'false' }}" class="{{ $loop->first ? 'is-active' : '' }}">{{ $filter[1] }}</button>@endforeach</div><p class="case-counter" role="status" data-case-counter data-singular="{{ $copy['countSingular'] }}" data-plural="{{ $copy['countPlural'] }}">{{ count($copy['studies']) }} {{ $copy['countPlural'] }}</p></div>
        <p class="cases-demo-note"><span aria-hidden="true">◇</span>{{ $copy['demoNote'] }}</p>
        <div class="cases-collection">
            @foreach ($copy['studies'] as $study)
                <details class="case-entry case-entry-{{ $loop->index }}" data-case-card="{{ $loop->index }}" data-category="{{ $study['category'] }}">
                    <summary>
                        <div class="case-poster case-poster-{{ $loop->index }}">
                            <div class="case-poster-top"><span>{{ $t['cases'][$loop->index]['client'] }}</span><span class="case-demo-badge">{{ $t['demoTag'] }}</span></div>
                            <div class="case-poster-art" aria-hidden="true">@if ($loop->index === 0)<div class="case-retail-lines">@foreach ($study['artWords'] as $word)<span>{{ $word }}</span>@endforeach</div><span class="case-retail-orbit"></span>@elseif ($loop->index === 1)<span class="case-social-quote">“</span><div class="case-social-words">@foreach ($study['artWords'] as $word)<span>{{ $word }}</span>@endforeach</div><span class="case-social-bubble">↗</span>@else<div class="case-data-bars">@foreach ([28, 58, 42, 78, 64, 95] as $height)<i style="--bar-height:{{ $height }}%"></i>@endforeach</div><div class="case-data-word">DATA<span>↗</span></div>@endif</div>
                            <div class="case-poster-result"><strong>{{ $t['cases'][$loop->index]['metric'] }}</strong><span>{{ $t['cases'][$loop->index]['metricLabel'] }}</span></div>
                        </div>
                        <div class="case-summary"><div><span class="service-eyebrow">0{{ $loop->iteration }} / {{ $study['categoryLabel'] }}</span><h3>{{ $study['title'] }}</h3><p>{{ $t['cases'][$loop->index]['desc'] }}</p></div><span class="case-open-link"><span class="case-open-copy">{{ $copy['openLabel'] }}</span><span class="case-close-copy">{{ $copy['closeLabel'] }}</span><i aria-hidden="true">↗</i></span></div>
                    </summary>
                    <div class="case-expanded"><div><span class="service-eyebrow">{{ $copy['challengeLabel'] }}</span><p>{{ $study['challenge'] }}</p></div><div><span class="service-eyebrow">{{ $copy['approachLabel'] }}</span><p>{{ $study['approach'] }}</p></div><div class="case-expanded-result"><span class="service-eyebrow">{{ $copy['resultLabel'] }} / {{ $t['demoTag'] }}</span><strong>{{ $t['cases'][$loop->index]['metric'] }}</strong><p>{{ $t['cases'][$loop->index]['metricLabel'] }}</p><a href="{{ $serviceUrls[$study['service']] }}">{{ $copy['serviceLabel'] }} →</a></div></div>
                </details>
            @endforeach
        </div>
        <p class="cases-empty" data-case-empty hidden>{{ $copy['empty'] }}</p>
    </section>

    <section class="cases-closing"><div class="service-width"><div><p class="service-eyebrow">{{ $copy['closingKicker'] }}</p><h2 class="preserve-lines">{{ $copy['closing'] }}</h2><p>{{ $copy['closingBody'] }}</p></div><a href="{{ $contactUrl }}"><span>{{ $copy['closingCta'] }}</span><b aria-hidden="true">↗</b></a></div></section>
</main>
@endsection
