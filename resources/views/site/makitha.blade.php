@extends('site.layout')

@section('content')
<main id="main-content" class="specialization makitha-page">
    <section class="mk-tech-opening">
        <div class="service-width">
            <div class="mk-tech-masthead">
                <a class="service-back" href="{{ $homeUrl.'#servicios' }}">← {{ $t['nav'][0] }}</a>
                <span>{{ $copy['kicker'] }}</span>
                <span>MAKITHA / FEAT.</span>
            </div>
            <div class="mk-tech-cover">
                <div>
                    <p class="service-eyebrow">Makitha / Tech Growth Partner</p>
                    <h1>{{ $copy['title'] }}<em>{{ $copy['accent'] }}</em></h1>
                    <p class="service-body">{{ $copy['intro'] }}</p>
                    <div class="mk-tech-actions"><a class="service-button" href="{{ $contactUrl }}">{{ $copy['cta'] }} ↗</a><a class="mk-tech-explore" href="#makitha-solutions">{{ $copy['explore'] }} ↓</a></div>
                </div>
                <div class="mk-growth-engine" aria-label="{{ $copy['engineTitle'] }}">
                    <div class="engine-heading"><img src="{{ asset('assets/makitha/wordmark-white.png') }}" alt="Makitha"><span>tech + strategy</span></div>
                    <div class="engine-diagram">
                        <div class="engine-connections" aria-hidden="true"></div>
                        <span class="engine-core">{{ $copy['engineCenter'] }}<b aria-hidden="true">↗</b></span>
                        @foreach ($copy['engineNodes'] as $node)
                            <span class="engine-node engine-node-{{ $loop->index }}"><i aria-hidden="true">{{ ['⌘', '↗', '↔', '✳'][$loop->index] }}</i>{{ $node }}</span>
                        @endforeach
                    </div>
                    <div class="engine-footer"><span>{{ $copy['engineTitle'] }}</span><i aria-hidden="true">↗</i></div>
                </div>
            </div>
            <div class="mk-tech-cover-footer"><span>{{ $copy['engineNote'] }}</span><span>{{ $copy['engineFoot'] }}</span></div>
        </div>
    </section>

    <section id="makitha-solutions" class="mk-solutions" data-panel-group="makitha-tech">
        <div class="service-width">
            <div class="mk-solutions-heading"><div><p class="service-eyebrow">{{ $copy['solutionsKicker'] }}</p><h2 class="preserve-lines">{{ $copy['solutionsTitle'] }}</h2></div><p class="service-body">{{ $copy['solutionsIntro'] }}</p></div>
            <div class="mk-solutions-workspace">
                <div class="mk-solution-tabs" role="tablist" aria-orientation="vertical" aria-label="{{ $copy['solutionsKicker'] }}">
                    @foreach ($copy['solutions'] as $solution)
                        <button type="button" role="tab" id="makitha-tab-{{ $loop->index }}" data-panel-tab="{{ $loop->index }}" aria-controls="makitha-panel-{{ $loop->index }}" aria-selected="{{ $loop->first ? 'true' : 'false' }}" class="{{ $loop->first ? 'is-active' : '' }}"><span>0{{ $loop->iteration }}</span>{{ $solution['short'] }}<i aria-hidden="true">↗</i></button>
                    @endforeach
                </div>
                <div class="mk-solution-panels">
                    @foreach ($copy['solutions'] as $solution)
                        <article id="makitha-panel-{{ $loop->index }}" data-panel="{{ $loop->index }}" role="tabpanel" aria-labelledby="makitha-tab-{{ $loop->index }}" @if (!$loop->first) hidden @endif>
                            <div class="mk-solution-art mk-solution-art-{{ $loop->index }}" aria-hidden="true">
                                <div class="mk-art-top"><span>makitha / {{ ['web', 'apps', 'growth', 'integrations', 'data', 'ai'][$loop->index] }}</span><span>● ● ●</span></div>
                                <div class="mk-art-shapes">
                                    @if ($loop->index === 0)
                                        <div class="mk-mini-web"><b>feat.</b><span></span><span></span><i>↗</i></div>
                                    @elseif ($loop->index === 1)
                                        <div class="mk-app-window"></div><div class="mk-app-window"></div><div class="mk-app-window"><b>⌘</b></div>
                                    @elseif ($loop->index === 2)
                                        <div class="mk-growth-ring"></div><div class="mk-growth-ring"></div><b class="mk-growth-arrow">↗</b>
                                    @elseif ($loop->index === 3)
                                        <div class="mk-integration-node">CRM</div><i class="mk-integration-link">↔</i><div class="mk-integration-node">DATA</div><i class="mk-integration-link">↔</i><div class="mk-integration-node">APPS</div>
                                    @elseif ($loop->index === 4)
                                        <div class="mk-data-chart">@foreach ([35, 58, 46, 75, 65, 92] as $height)<i style="--height:{{ $height }}%"></i>@endforeach</div><b class="mk-data-signal">↗</b>
                                    @else
                                        <span class="mk-ai-input">↳</span><div class="mk-ai-core">AI</div><span class="mk-ai-output">↗</span>
                                    @endif
                                </div>
                                <p>{{ $solution['word'] }}</p>
                            </div>
                            <div class="mk-solution-description"><p class="service-eyebrow">0{{ $loop->iteration }} / {{ $solution['title'] }}</p><h3>{{ $solution['headline'] }}</h3><p>{{ $solution['body'] }}</p><ul>@foreach ($solution['outputs'] as $output)<li>{{ $output }}</li>@endforeach</ul></div>
                        </article>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    <section class="mk-build-cycle service-width">
        <div class="mk-cycle-heading"><p class="service-eyebrow">{{ $copy['processKicker'] }}</p><h2 class="preserve-lines">{{ $copy['processTitle'] }}</h2><span aria-hidden="true">↗</span></div>
        <ol class="mk-cycle-steps">@foreach ($copy['process'] as $step)<li><span class="mk-cycle-number">0{{ $loop->iteration }}</span><h3>{{ $step[0] }}</h3><p>{{ $step[1] }}</p></li>@endforeach</ol>
    </section>

    <section class="mk-feat-bridge"><div class="service-width"><div class="mk-brand-pair"><img src="{{ asset('assets/feat-white.svg') }}" alt="Feat."><span aria-hidden="true">+</span><img src="{{ asset('assets/makitha/wordmark-white.png') }}" alt="Makitha"></div><div><h2 class="preserve-lines">{{ $copy['bridgeTitle'] }}</h2><p class="service-body">{{ $copy['bridgeBody'] }}</p><a href="https://www.makitha.com/?lang={{ $locale }}" class="mk-website-link">{{ $copy['websiteCta'] }} <span aria-hidden="true">↗</span></a></div></div></section>

    <section class="mk-tech-closing"><div class="service-width"><p class="service-eyebrow">Intelligence for scalable growth.</p><h2 class="preserve-lines">{{ $copy['closing'] }}</h2><a href="{{ $contactUrl }}">{{ $copy['closingCta'] }} <span aria-hidden="true">↗</span></a><div class="mk-tech-next"><span>{{ $copy['next'] }}</span><a href="{{ $serviceUrls['experience'] }}">{{ $copy['nextName'] }} →</a></div></div></section>
</main>
@endsection
