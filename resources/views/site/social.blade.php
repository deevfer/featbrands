@extends('site.layout')

@section('content')
<main id="main-content" class="specialization social-page">
    <section class="social-opening service-width">
        <div class="social-intro"><a class="service-back" href="{{ $homeUrl.'#servicios' }}">← {{ $t['nav'][0] }}</a><p class="service-eyebrow">{{ $copy['name'] }} / 05</p><h1>{{ $copy['title'] }} <em>{{ $copy['accent'] }}</em></h1><p class="service-body">{{ $copy['intro'] }}</p><a class="service-button" href="{{ $contactUrl }}">{{ $copy['cta'] }} ↗</a></div>
        <div class="social-collage" aria-label="{{ $copy['postLabel'] }}">
            <div class="social-post"><div class="post-account"><img src="{{ asset('assets/feat-black.svg') }}" alt="Feat."><span>● ● ●</span></div><p>{{ $copy['postWords'][0] }}<em>{{ $copy['postWords'][1] }}</em></p><div class="post-marks" aria-hidden="true"><span>♡ ↗</span><span>⌑</span></div></div>
            <div class="social-reply"><span aria-hidden="true">“</span><p>{{ $copy['reply'] }}<strong>{{ $copy['replyAccent'] }}</strong></p><i aria-hidden="true">↳</i></div>
            <div class="social-sticker" aria-hidden="true">let’s<br><em>talk.</em> ↗</div>
            <p class="collage-caption">{{ $copy['postFoot'] }}</p>
        </div>
        <div class="social-cover-note"><span>{{ $copy['kicker'] }}</span><span aria-hidden="true">↓</span></div>
    </section>

    <section class="social-lab" data-panel-group="social">
        <div class="service-width"><div class="social-lab-head"><p class="service-eyebrow">{{ $copy['labKicker'] }}</p><h2>{{ $copy['labTitle'] }}</h2><p class="service-body">{{ $copy['labBody'] }}</p></div>
            <div class="social-mode-tabs" role="tablist" aria-label="{{ $copy['labTitle'] }}">@foreach ($copy['modes'] as $mode)<button type="button" role="tab" id="social-tab-{{ $loop->index }}" data-panel-tab="{{ $loop->index }}" aria-controls="social-panel-{{ $loop->index }}" aria-selected="{{ $loop->first ? 'true' : 'false' }}" class="{{ $loop->first ? 'is-active' : '' }}">0{{ $loop->iteration }} / {{ $mode['label'] }}<span aria-hidden="true">↗</span></button>@endforeach</div>
            @foreach ($copy['modes'] as $mode)
                <div id="social-panel-{{ $loop->index }}" class="social-mode-content" data-panel="{{ $loop->index }}" role="tabpanel" aria-labelledby="social-tab-{{ $loop->index }}" @if (!$loop->first) hidden @endif>
                    <div class="social-example social-example-{{ $loop->index }}"><span class="service-eyebrow">{{ $mode['tag'] }}</span><div class="social-graphic" aria-hidden="true">@if ($loop->index === 0)<span>!</span><i></i>@elseif ($loop->index === 1)<span>“</span><span>”</span>@else<span>↗</span><i></i>@endif</div><p>{{ $mode['headline'] }}</p><span class="social-example-label">feat. / social</span></div>
                    <div class="social-mode-copy"><p class="service-eyebrow">{{ $mode['format'] }}</p><h3>{{ $mode['caption'] }}</h3><p>{{ $mode['body'] }}</p><span class="social-mode-index" aria-hidden="true">0{{ $loop->iteration }}</span></div>
                </div>
            @endforeach
        </div>
    </section>

    <section class="social-rhythm service-width"><p class="service-eyebrow">{{ $copy['rhythmKicker'] }}</p><h2>{{ $copy['rhythmTitle'] }}</h2><div class="rhythm-line">@foreach ($copy['rhythm'] as $step)<article><div class="rhythm-node"><span>0{{ $loop->iteration }}</span><i aria-hidden="true">{{ $loop->last ? '↺' : '→' }}</i></div><h3>{{ $step[0] }}</h3><p>{{ $step[1] }}</p></article>@endforeach</div></section>

    <section class="social-measure"><div class="service-width"><div class="social-measure-type"><span>likes.</span><strong>{{ $copy['measureWord'] }}</strong><i aria-hidden="true">↗</i></div><div><p class="service-eyebrow">Social intelligence</p><h2>{{ $copy['measureTitle'] }}</h2><p class="service-body">{{ $copy['measureBody'] }}</p><ul>@foreach ($copy['metrics'] as $metric)<li><span aria-hidden="true">↳</span>{{ $metric }}</li>@endforeach</ul></div></div></section>

    <section class="social-conversation service-width"><div class="closing-question">{{ $copy['closingQuestion'] }}</div><div class="closing-answer"><p>{{ $copy['closingAnswer'] }}</p><a href="{{ $contactUrl }}">{{ $copy['closingCta'] }} ↗</a></div><div class="social-next"><span>{{ $copy['next'] }}</span><a href="{{ $serviceUrls['makitha'] }}">{{ $copy['nextName'] }} →</a></div></section>
</main>
@endsection
