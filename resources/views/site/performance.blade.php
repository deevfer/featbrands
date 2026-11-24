@extends('site.layout')

@section('content')
<main id="main-content" class="specialization performance-page">
    <section class="performance-opening">
        <div class="service-width">
            <a class="service-back" href="{{ $homeUrl.'#servicios' }}"><x-site-icon name="general.arrow_back">←</x-site-icon> {{ $t['nav'][0] }}</a>
            <div class="performance-cover">
                <div class="performance-cover-copy">
                    <p class="service-eyebrow">{{ $p['kicker'] }} / 01</p>
                    <h1>{{ $p['h1'] }}</h1>
                    <div class="performance-answer">
                        <span class="service-eyebrow">{{ $p['answerTag'] }}</span>
                        <p>{{ $p['answer'] }}</p>
                    </div>
                    <a class="service-button" href="{{ $p['ctaHref'] ?? $contactUrl }}">{{ $p['ctaBtn'] }} <x-site-icon name="general.arrow_out">↗</x-site-icon></a>
                </div>
                <div class="performance-dashboard" aria-hidden="true">
                    <div class="dashboard-top"><span>feat. / performance</span><span>01—PC</span></div>
                    <div class="dashboard-kpis"><span>CPC</span><span>CPA</span><span>ROAS</span></div>
                    <div class="dashboard-chart">@foreach ([38, 52, 44, 63, 58, 76, 70, 92] as $bar)<i style="height:{{ $bar }}%"></i>@endforeach</div>
                    <svg class="dashboard-line" viewBox="0 0 300 120" preserveAspectRatio="none"><polyline points="0,100 40,84 80,90 120,62 160,68 200,40 240,46 300,10" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" vector-effect="non-scaling-stroke"/></svg>
                    <span class="dashboard-badge"><x-site-icon name="general.arrow_out">↗</x-site-icon></span>
                </div>
            </div>
            <nav class="performance-cover-foot" aria-label="Breadcrumb">
                <span><a href="{{ $homeUrl }}">Feat.</a> / <a href="{{ $homeUrl.'#servicios' }}">{{ $p['crumbServices'] }}</a> / <span aria-current="page">{{ $p['crumbCurrent'] }}</span></span>
                <span aria-hidden="true"><x-site-icon name="general.arrow_down">↓</x-site-icon></span>
            </nav>
        </div>
    </section>

    <section class="performance-challenge service-width">
        <h2 class="service-eyebrow">{{ $p['problemH'] }}</h2>
        <p>{{ $p['problem'] }}</p>
    </section>

    <section class="performance-deliverables service-width">
        <h2 class="service-eyebrow">{{ $p['capsH'] }}</h2>
        <div class="performance-cap-list">
            @foreach ($p['caps'] as $cap)
                <article><span class="performance-cap-number">{{ $cap['n'] }}</span><h3>{{ $cap['title'] }}</h3><p>{{ $cap['desc'] }}</p><span class="performance-cap-arrow" aria-hidden="true"><x-site-icon name="general.arrow_out">↗</x-site-icon></span></article>
            @endforeach
        </div>
    </section>

    <section class="performance-method">
        <div class="service-width">
            <h2 class="service-eyebrow">{{ $p['processH'] }}</h2>
            <div class="performance-steps">
                @foreach ($p['process'] as $step)
                    <article><div class="performance-step-node"><span>{{ $step['n'] }}</span><i aria-hidden="true">@if ($loop->last)<x-site-icon name="general.arrow_out">↗</x-site-icon>@else<x-site-icon name="general.arrow_next">→</x-site-icon>@endif</i></div><h3>{{ $step['title'] }}</h3><p>{{ $step['desc'] }}</p></article>
                @endforeach
            </div>
        </div>
    </section>

    <section class="performance-results">
        <div class="service-width">
            <h2 class="service-eyebrow">{{ $p['casesH'] }}</h2>
            <div class="performance-case-grid">
                @foreach ($p['cases'] as $c)
                    <article class="performance-case"><div class="performance-case-top"><span>{{ $c['client'] }}</span><span class="performance-demo">{{ $t['demoTag'] }}</span></div><strong>{{ $c['metric'] }}</strong><span class="performance-case-label">{{ $c['metricLabel'] }}</span><p>{{ $c['desc'] }}</p></article>
                @endforeach
            </div>
        </div>
    </section>

    <!-- FAQ (FAQPage schema) -->
    <section class="performance-faq service-width">
        <h2>{{ $p['faqH'] }}</h2>
        <div class="performance-faq-list">
            @foreach ($p['faq'] as $f)
                <div class="performance-faq-item">
                    <button type="button" data-faq-toggle aria-controls="faq-answer-{{ $loop->index }}" aria-expanded="{{ $loop->first ? 'true' : 'false' }}"><span class="question-number">0{{ $loop->iteration }}</span><span>{{ $f['q'] }}</span><b data-faq-sign aria-hidden="true">{{ $loop->first ? '–' : '+' }}</b></button>
                    <div data-faq-answer {{ $loop->first ? '' : 'hidden' }} id="faq-answer-{{ $loop->index }}"><p>{{ $f['a'] }}</p></div>
                </div>
            @endforeach
        </div>
    </section>

    <!-- CTA -->
    <section class="performance-closing">
        <div class="service-width">
            <h2>{{ $p['ctaTitle'] }}</h2>
            <p class="performance-closing-body">{{ $p['ctaBody'] }}</p>
            <a class="performance-contact" href="{{ $p['ctaHref'] ?? $contactUrl }}"><span>{{ $p['ctaBtn'] }}</span><b aria-hidden="true"><x-site-icon name="general.arrow_out">↗</x-site-icon></b></a>
            <div class="performance-related">
                @foreach ($p['related'] as $r)
                    <a href="{{ $r['href'] ?? $serviceUrls[$r['title'] === 'Experience & Tech' ? 'experience' : 'social'] }}" style="--unit-color:{{ $r['bg'] }}"><span class="service-eyebrow">{{ $r['tag'] }}</span><strong>{{ $r['title'] }}</strong><span class="performance-related-desc">{{ $r['desc'] }}</span><span class="performance-related-cta">{{ $r['cta'] }} <x-site-icon name="general.arrow_next">→</x-site-icon></span></a>
                @endforeach
            </div>
        </div>
    </section>
</main>
@endsection
