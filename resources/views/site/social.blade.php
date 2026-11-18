@extends('site.layout')

@section('content')
<main id="main-content" class="specialization social-page">
    <section class="social-opening service-width">
        <div class="social-intro"><a class="service-back" href="{{ $homeUrl.'#servicios' }}"><x-site-icon name="general.arrow_back">←</x-site-icon> {{ $t['nav'][0] }}</a><p class="service-eyebrow">{{ $copy['name'] }} / 05</p><h1>{{ $copy['title'] }} <em>{{ $copy['accent'] }}</em></h1><p class="service-body">{{ $copy['intro'] }}</p><a class="service-button" href="{{ $copy['ctaHref'] }}">{{ $copy['cta'] }} <x-site-icon name="general.arrow_out">↗</x-site-icon></a></div>
        <div class="social-collage" aria-label="{{ $copy['postLabel'] }}">
            <div class="social-post"><div class="post-account"><img src="{{ $brandAssets['logo_dark'] }}" alt="Feat."><span><x-site-icon name="social.window_dots">● ● ●</x-site-icon></span></div><p>{{ $copy['postWords'][0] }}<em>{{ $copy['postWords'][1] }}</em></p><div class="post-marks" aria-hidden="true"><span><x-site-icon name="social.post_like">♡ ↗</x-site-icon></span><span><x-site-icon name="social.post_save">⌑</x-site-icon></span></div></div>
            <div class="social-reply"><span aria-hidden="true"><x-site-icon name="social.reply_quote">“</x-site-icon></span><p>{{ $copy['reply'] }}<strong>{{ $copy['replyAccent'] }}</strong></p><i aria-hidden="true"><x-site-icon name="social.reply">↳</x-site-icon></i></div>
            <div class="social-sticker" aria-hidden="true">{{ $copy['stickerTop'] }}<br><em>{{ $copy['stickerAccent'] }}</em> <x-site-icon name="general.arrow_out">↗</x-site-icon></div>
            <p class="collage-caption">{{ $copy['postFoot'] }}</p>
        </div>
        <div class="social-cover-note"><span>{{ $copy['kicker'] }}</span><span aria-hidden="true"><x-site-icon name="general.arrow_down">↓</x-site-icon></span></div>
    </section>

    <section class="social-lab" data-panel-group="social">
        <div class="service-width"><div class="social-lab-head"><p class="service-eyebrow">{{ $copy['labKicker'] }}</p><h2>{{ $copy['labTitle'] }}</h2><p class="service-body">{{ $copy['labBody'] }}</p></div>
            <div class="social-mode-tabs" role="tablist" aria-label="{{ $copy['labTitle'] }}">@foreach ($copy['modes'] as $mode)<button type="button" role="tab" id="social-tab-{{ $loop->index }}" data-panel-tab="{{ $loop->index }}" aria-controls="social-panel-{{ $loop->index }}" aria-selected="{{ $loop->first ? 'true' : 'false' }}" class="{{ $loop->first ? 'is-active' : '' }}">0{{ $loop->iteration }} / {{ $mode['label'] }}<span aria-hidden="true"><x-site-icon name="general.arrow_out">↗</x-site-icon></span></button>@endforeach</div>
            @foreach ($copy['modes'] as $mode)
                <div id="social-panel-{{ $loop->index }}" class="social-mode-content" data-panel="{{ $loop->index }}" role="tabpanel" aria-labelledby="social-tab-{{ $loop->index }}" @if (!$loop->first) hidden @endif>
                    <div class="social-example social-example-{{ $loop->index }}"><span class="service-eyebrow">{{ $mode['tag'] }}</span><div class="social-graphic" aria-hidden="true">@if ($loop->index === 0)<span><x-site-icon name="social.mode_1">!</x-site-icon></span><i></i>@elseif ($loop->index === 1)<span><x-site-icon name="social.mode_2_open">“</x-site-icon></span><span><x-site-icon name="social.mode_2_close">”</x-site-icon></span>@else<span><x-site-icon name="social.mode_3">↗</x-site-icon></span><i></i>@endif</div><p>{{ $mode['headline'] }}</p><span class="social-example-label">{{ $copy['exampleLabel'] }}</span></div>
                    <div class="social-mode-copy"><p class="service-eyebrow">{{ $mode['format'] }}</p><h3>{{ $mode['caption'] }}</h3><p>{{ $mode['body'] }}</p><span class="social-mode-index" aria-hidden="true">0{{ $loop->iteration }}</span></div>
                </div>
            @endforeach
        </div>
    </section>

    <section class="social-rhythm service-width"><p class="service-eyebrow">{{ $copy['rhythmKicker'] }}</p><h2>{{ $copy['rhythmTitle'] }}</h2><div class="rhythm-line">@foreach ($copy['rhythm'] as $step)<article><div class="rhythm-node"><span>0{{ $loop->iteration }}</span><i aria-hidden="true">@if ($loop->last)<x-site-icon name="social.rhythm_last">↺</x-site-icon>@else<x-site-icon name="social.rhythm_step">→</x-site-icon>@endif</i></div><h3>{{ $step[0] }}</h3><p>{{ $step[1] }}</p></article>@endforeach</div></section>

    <section class="social-measure"><div class="service-width"><div class="social-measure-type"><span>{{ $copy['measureStrike'] }}</span><strong>{{ $copy['measureWord'] }}</strong><i aria-hidden="true"><x-site-icon name="general.arrow_out">↗</x-site-icon></i></div><div><p class="service-eyebrow">{{ $copy['measureKicker'] }}</p><h2>{{ $copy['measureTitle'] }}</h2><p class="service-body">{{ $copy['measureBody'] }}</p><ul>@foreach ($copy['metrics'] as $metric)<li><span aria-hidden="true"><x-site-icon name="social.metric">↳</x-site-icon></span>{{ $metric }}</li>@endforeach</ul></div></div></section>

    <section class="social-conversation service-width"><div class="closing-question">{{ $copy['closingQuestion'] }}</div><div class="closing-answer"><p>{{ $copy['closingAnswer'] }}</p><a href="{{ $copy['closingHref'] }}">{{ $copy['closingCta'] }} <x-site-icon name="general.arrow_out">↗</x-site-icon></a></div><div class="social-next"><span>{{ $copy['next'] }}</span><a href="{{ $copy['nextHref'] }}">{{ $copy['nextName'] }} <x-site-icon name="general.arrow_next">→</x-site-icon></a></div></section>
</main>
@endsection
