@extends('site.layout')

@section('content')
<main id="main-content" class="specialization about-page">
    <section class="about-opening">
        <div class="service-width"><p class="service-eyebrow">{{ $copy['kicker'] }}</p><h1>{{ $copy['title'] }}<em>{{ $copy['accent'] }}</em></h1><p class="about-opening-intro">{{ $copy['intro'] }}</p>
            <div class="about-team-map" aria-label="{{ $copy['heroNote'] }}"><div class="about-map-ring about-map-ring-one" aria-hidden="true"></div><div class="about-map-ring about-map-ring-two" aria-hidden="true"></div><div class="about-map-core"><img src="{{ asset('assets/feat-black.svg') }}" alt="Feat."><span>{{ $copy['heroNote'] }}</span></div>@foreach ($serviceLinks as $service)<a class="about-map-specialty about-map-specialty-{{ $loop->index }}" href="{{ $service['href'] }}" style="--specialty-color:{{ $service['color'] }}"><span>0{{ $loop->iteration }}</span><strong>{{ $service['title'] }}</strong><i aria-hidden="true">↗</i></a>@endforeach</div>
            <a class="about-scroll-link" href="#colaboracion">{{ $copy['scrollLabel'] }} <span aria-hidden="true">↓</span></a>
        </div>
    </section>

    <section id="colaboracion" class="about-manifesto"><div class="service-width"><div class="about-manifesto-top"><p class="service-eyebrow">{{ $copy['manifestoKicker'] }}</p><span aria-hidden="true">✳</span></div><h2>{{ $copy['manifesto'] }}</h2><p class="about-manifesto-body">{{ $copy['manifestoBody'] }}</p><div class="about-principles">@foreach ($copy['principles'] as $principle)<article><span>0{{ $loop->iteration }}</span><h3>{{ $principle[0] }}</h3><p>{{ $principle[1] }}</p></article>@endforeach</div></div></section>

    <section class="about-specialties service-width"><div class="about-specialties-heading"><p class="service-eyebrow">{{ $copy['servicesKicker'] }}</p><h2 class="preserve-lines">{{ $copy['servicesTitle'] }}</h2><p class="service-body">{{ $copy['servicesBody'] }}</p></div><div class="about-specialty-list">@foreach ($serviceLinks as $service)<a href="{{ $service['href'] }}" class="about-specialty-row" style="--specialty-color:{{ $service['color'] }}"><span class="about-specialty-number">0{{ $loop->iteration }}</span><div><h3>{{ $service['title'] }}</h3><p>{{ $t['services'][$loop->index]['desc'] }}</p></div><span class="about-specialty-cta">{{ $copy['serviceCta'] }} <i aria-hidden="true">↗</i></span></a>@endforeach</div></section>

    <section class="about-brands"><div class="service-width"><p class="service-eyebrow">{{ $copy['clientsKicker'] }}</p><h2>{{ $t['clientsTitle'] }}</h2><p>{{ $copy['clientsBody'] }}</p><div class="about-brand-names">@foreach ($t['clients'] as $client)<span>{{ $client }}</span>@endforeach</div></div></section>

    <section id="reconocimientos" class="about-awards service-width"><div class="about-awards-heading"><p class="service-eyebrow">{{ $copy['awardsKicker'] }}</p><h2 class="preserve-lines">{{ $copy['awardsTitle'] }}</h2></div><div class="about-award-list">@foreach ($t['proof'] as $award)<article><div class="about-award-image"><img src="{{ asset('uploads/proof-bg-'.$loop->index.'.webp') }}" alt="{{ $award['label'] }}" loading="lazy"></div><div><strong>{{ $award['big'] }}</strong><p>{{ $award['label'] }}</p></div></article>@endforeach</div></section>

    <section class="about-presence"><div class="service-width"><div class="about-presence-heading"><div><p class="service-eyebrow">{{ $copy['marketsKicker'] }}</p><h2 class="preserve-lines">{{ $copy['marketsTitle'] }}</h2></div><p class="service-body">{{ $copy['marketsBody'] }}</p></div><div class="about-offices">@foreach ($t['offices'] as $office)<article class="about-office {{ $office['mk'] === $market ? 'is-active' : '' }}" data-office="{{ $office['mk'] }}" role="button" tabindex="0" aria-expanded="{{ $office['mk'] === $market ? 'true' : 'false' }}"><div class="about-office-top"><span>{{ $office['code'] }}</span><span data-office-sign>{{ $office['mk'] === $market ? '– ' : '+ ' }}</span></div><img src="{{ asset('assets/ill-'.$office['mk'].'.svg') }}" alt="" aria-hidden="true"><h3>{{ $office['city'] }}</h3><p class="about-office-tag">{{ $office['tag'] }}</p><div data-office-details @if ($office['mk'] !== $market) hidden @endif><p>{{ $office['message'] }}</p></div></article>@endforeach</div></div></section>

    <section class="about-closing service-width"><h2 class="preserve-lines">{{ $copy['closing'] }}</h2><a class="about-contact-link" href="{{ $contactUrl }}">{{ $copy['closingCta'] }} <span aria-hidden="true">↗</span></a><div class="about-work-link"><span>{{ $copy['workLink'] }}</span><a href="{{ $agencyUrls['cases'] }}">{{ $copy['workName'] }} →</a></div></section>
</main>
@endsection
