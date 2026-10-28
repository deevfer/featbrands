@extends('site.layout')

@section('content')
<main id="main-content" class="specialization brand-page">
    <section class="brand-opening">
        <div class="service-width brand-cover">
            <div class="brand-cover-copy">
                <a class="service-back" href="{{ $homeUrl.'#servicios' }}">← {{ $t['nav'][0] }}</a>
                <p class="service-eyebrow">{{ $copy['name'] }} / 02</p>
                <h1>{{ $copy['title'] }} <em>{{ $copy['accent'] }}</em></h1>
                <div class="brand-opening-bottom"><p>{{ $copy['intro'] }}</p><a class="service-round-link" href="#brand-questions" aria-label="{{ $copy['questionsTitle'] }}">↓</a></div>
            </div>
            <div class="brand-specimen" aria-label="{{ $copy['sheet'] }}">
                <div class="brand-paper">
                    <div class="specimen-top"><span>feat. / brand studio</span><span>02—BC</span></div>
                    <span class="brand-letter" aria-hidden="true">Aa<span>®</span></span>
                    <p class="specimen-note">{{ $copy['specimen'] }}</p>
                    <div class="brand-swatches" aria-hidden="true"><i></i><i></i><i></i><i></i></div>
                    <div class="specimen-bottom"><span>Bellinzo / Miller / Space Grotesk</span><span>↗</span></div>
                </div>
                <span class="brand-seal">{{ $copy['stamp'] }}</span>
                <p class="brand-paper-caption">{{ $copy['note'] }}</p>
            </div>
        </div>
        <div class="brand-cover-line service-width"><span>{{ $copy['kicker'] }}</span><span>{{ $copy['lead'] }}</span></div>
    </section>

    <section id="brand-questions" class="brand-questions service-width">
        <div><p class="service-eyebrow">Strategy first.</p><h2>{{ $copy['questionsTitle'] }}</h2><p class="service-body">{{ $copy['questionsIntro'] }}</p></div>
        <div class="brand-question-list">
            @foreach ($copy['questions'] as $question)
                <details name="brand-questions" @if ($loop->first) open @endif>
                    <summary><span class="question-number">0{{ $loop->iteration }}</span><span>{{ $question[0] }}</span><b aria-hidden="true">+</b></summary>
                    <p>{{ $question[1] }}</p>
                </details>
            @endforeach
        </div>
    </section>

    <section class="brand-system">
        <div class="service-width">
            <div class="brand-system-heading"><p class="service-eyebrow">{{ $copy['systemKicker'] }}</p><h2>{{ $copy['systemTitle'] }}<em>{{ $copy['systemAccent'] }}</em></h2></div>
            <div class="brand-system-board">
                <article class="identity-sheet"><span class="service-eyebrow">01 / {{ $copy['identityLabel'] }}</span><div class="identity-letter" aria-hidden="true">F<span>.</span></div><div class="identity-rule" aria-hidden="true"></div><p>{{ $copy['identityBody'] }}</p></article>
                <article class="voice-sheet"><span class="service-eyebrow">02 / {{ $copy['voiceLabel'] }}</span><blockquote>“{{ $copy['voiceQuote'] }}”</blockquote><p>{{ $copy['voiceBody'] }}</p></article>
                <article class="ecosystem-sheet"><span class="service-eyebrow">03 / {{ $copy['ecosystemLabel'] }}</span><h3 class="preserve-lines">{{ $copy['ecosystemTitle'] }}</h3><div class="brand-orbits" aria-hidden="true"><i></i><i></i><i></i><b>feat.</b></div><p>{{ $copy['ecosystemBody'] }}</p></article>
            </div>
        </div>
    </section>

    <section class="brand-workshop service-width">
        <div class="workshop-margin"><span class="workshop-asterisk" aria-hidden="true">✳</span><p class="service-eyebrow">{{ $copy['workshopKicker'] }}</p></div>
        <div><h2>{{ $copy['workshopTitle'] }}</h2><p class="service-body">{{ $copy['workshopBody'] }}</p><ol class="brand-workshop-steps">@foreach ($copy['workshopSteps'] as $step)<li><span>0{{ $loop->iteration }}</span>{{ $step }}</li>@endforeach</ol></div>
    </section>

    <section class="brand-signoff"><div class="service-width"><p>{{ $copy['closing'] }}</p><a href="{{ $contactUrl }}">{{ $copy['closingCta'] }}<span aria-hidden="true">↗</span></a><div class="brand-next"><span>{{ $copy['next'] }}</span><a href="{{ $serviceUrls['social'] }}">{{ $copy['nextName'] }} →</a></div></div></section>
</main>
@endsection
