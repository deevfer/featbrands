@extends('site.layout')

@section('content')
<main id="main-content">
    <!-- HERO -->
    <section style="position:relative;overflow:clip">
      <div style="position:absolute;inset:0;background:linear-gradient(180deg,#FBEFE4 0%,#E8E6E2 48%,#C0CEDC 100%);pointer-events:none"></div>
      <div style="position:absolute;inset:0;background-image:url('data:image/svg+xml,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%22180%22%20height%3D%22180%22%3E%3Cfilter%20id%3D%22n%22%3E%3CfeTurbulence%20type%3D%22fractalNoise%22%20baseFrequency%3D%220.9%22%20numOctaves%3D%222%22%20stitchTiles%3D%22stitch%22%2F%3E%3C%2Ffilter%3E%3Crect%20width%3D%22180%22%20height%3D%22180%22%20filter%3D%22url(%23n)%22%20opacity%3D%220.55%22%2F%3E%3C%2Fsvg%3E');mix-blend-mode:overlay;opacity:.6;pointer-events:none"></div>
      <div style="position:relative;max-width:1320px;margin:0 auto;padding:76px 36px 96px">
        <div data-grid="hero" style="display:grid;grid-template-columns:minmax(0,65fr) minmax(0,35fr);gap:clamp(40px,5vw,80px);align-items:center">
          <div style="animation:featUp .9s ease both">
            <span style="font-size:12px;letter-spacing:.34em;text-transform:uppercase;color:#6B6B6B">{{ $t['hero']['eyebrow'] }}</span>
            <h1 style="font-family:'Bellinzo',serif;font-weight:700;font-size:clamp(38px,5.8vw,84px);line-height:1;letter-spacing:-.02em;margin-top:28px;max-width:16ch;text-wrap:balance;color:#000000;hyphens:none">{{ $t['hero']['titlePre'] }}<span style="position:relative;display:inline-block">{{ $t['hero']['titleAccent'] }}<span style="position:absolute;left:0;bottom:-.06em;height:.16em;width:100%;overflow:hidden;background-image:url('data:image/svg+xml,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%2248%22%20height%3D%2214%22%20viewBox%3D%220%200%2048%2014%22%3E%3Cpath%20d%3D%22M0%207%20Q12%200%2024%207%20T48%207%22%20fill%3D%22none%22%20stroke%3D%22%23FA551E%22%20stroke-width%3D%223.5%22%20stroke-linecap%3D%22round%22%2F%3E%3C%2Fsvg%3E');background-repeat:repeat-x;background-size:48px 100%;animation:featUnderline 1.1s cubic-bezier(.22,1,.36,1) .7s both,featWave 1.6s linear infinite"></span></span></h1>
            <p style="font-size:clamp(17px,1.8vw,20px);color:#474747;max-width:42ch;line-height:1.6;margin-top:40px">{{ $t['hero']['sub'] }}</p>
            <div style="display:flex;gap:14px;margin-top:36px;flex-wrap:wrap"><a href="{{ $contactUrl }}" style="background:#FB6C26;color:#000000;border:none;cursor:pointer;font-family:'Space Grotesk',sans-serif;font-weight:600;font-size:15px;padding:15px 28px;border-radius:100px" class="prototype-hover-2">{{ $t['hero']['ctaPrimary'] }}</a><a href="{{ $performanceUrl }}" style="background:transparent;color:#000000;border:1px solid rgba(0,0,0,.35);cursor:pointer;font-family:'Space Grotesk',sans-serif;font-weight:600;font-size:15px;padding:15px 28px;border-radius:100px" class="prototype-hover-3">{{ $t['hero']['ctaSecondary'] }}</a></div>
          </div>
          <div style="display:flex;flex-direction:column;gap:26px;width:min(480px,100%);margin-left:auto" data-deck>
            <div style="position:relative;height:440px">
              @foreach ($deckCards as $c)
                <div data-deck-open="{{ $loop->index }}" class="deck-card" data-deck-card="{{ $loop->index }}" data-service-url="{{ $c['href'] }}" role="link" tabindex="0" style="background:{{ $c['background'] }};color:{{ $c['foreground'] }}">
                  <div style="display:flex;flex-direction:column;gap:12px;min-width:0;overflow:hidden">
                    <span style="font-family:'Bellinzo',serif;font-weight:700;font-size:38px;line-height:.9">{{ $c['num'] }}</span>
                    <h3 style="font-family:'Bellinzo',serif;font-weight:700;font-size:29px;line-height:1;letter-spacing:-.01em">{{ $c['title'] }}</h3>
                    <p style="font-family:'Miller Text Italic',serif;font-style:italic;font-size:15px;line-height:1.4;opacity:.85">{{ $c['desc'] }}</p>
                  </div>
                  <div style="display:flex;justify-content:flex-end;margin-top:12px">
                    <button type="button" data-deck-open="{{ $loop->index }}" style="background:{{ $c['foreground'] }};color:{{ $c['background'] }};border:none;cursor:pointer;font-family:inherit;font-weight:600;font-size:14px;padding:12px 22px;border-radius:100px" class="prototype-hover-4">{{ $c['cta'] }}</button>
                  </div>
                </div>
              @endforeach
            </div>
            <div style="display:flex;align-items:center;justify-content:space-between">
              <div style="display:flex;gap:8px;align-items:center">
                @foreach ($t['services'] as $d)
                  <button type="button" data-deck-dot="{{ $loop->index }}" class="deck-dot {{ $loop->first ? 'is-active' : '' }}" aria-label="{{ $d['title'] }}"></button>
                @endforeach
              </div>
              <div style="display:flex;align-items:center;gap:12px">
                <span style="font-family:'Space Grotesk',sans-serif;font-size:13px;letter-spacing:.08em;color:#474747"><span data-deck-counter aria-live="off">01 — 05</span></span>
                <button type="button" data-deck-prev aria-label="Anterior" style="width:44px;height:44px;border-radius:50%;border:1px solid rgba(0,0,0,.3);background:transparent;cursor:pointer;font-size:16px;color:#000000;display:flex;align-items:center;justify-content:center" class="prototype-hover-5">←</button>
                <button type="button" data-deck-next aria-label="Siguiente" style="width:44px;height:44px;border-radius:50%;border:none;background:#000000;color:#FFFFFF;cursor:pointer;font-size:16px;display:flex;align-items:center;justify-content:center" class="prototype-hover-6">→</button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- PROOF BAR -->
    <section style="position:relative;background:#C7C4BE;overflow:clip">
      <div style="position:absolute;inset:0;background-image:url('data:image/svg+xml,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%22180%22%20height%3D%22180%22%3E%3Cfilter%20id%3D%22n%22%3E%3CfeTurbulence%20type%3D%22fractalNoise%22%20baseFrequency%3D%220.9%22%20numOctaves%3D%222%22%20stitchTiles%3D%22stitch%22%2F%3E%3C%2Ffilter%3E%3Crect%20width%3D%22180%22%20height%3D%22180%22%20filter%3D%22url(%23n)%22%20opacity%3D%220.55%22%2F%3E%3C%2Fsvg%3E');mix-blend-mode:overlay;opacity:.6;pointer-events:none"></div>
      <div style="position:relative;max-width:1320px;margin:0 auto;padding:76px 36px 84px">
        <div style="display:flex;justify-content:space-between;align-items:flex-end;flex-wrap:wrap;gap:16px;margin-bottom:40px">
          <div>
            <span style="font-size:12px;letter-spacing:.34em;text-transform:uppercase;color:#6B665B">{{ $t['proofKicker'] }}</span>
            <h2 style="font-family:'Bellinzo',serif;font-weight:700;font-size:clamp(30px,3.8vw,52px);line-height:1;letter-spacing:-.02em;margin-top:14px;color:#000000">{{ $t['proofTitle'] }}</h2>
          </div>
        </div>
        <div data-grid="proof" style="display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:16px">
          @foreach ($t['proof'] as $p)
            <div style="min-width:0;background:#FFFFFF;border-radius:24px;padding:16px;display:flex;flex-direction:column;gap:16px;box-shadow:0 16px 40px rgba(0,0,0,.1)">
              <div style="height:190px;border-radius:16px;overflow:hidden;background:#FBEFE4">
                <img src="{{ asset('uploads/proof-bg-'.$loop->index.'.webp') }}" alt="{{ $p['label'] }}" loading="lazy" class="proof-image">
              </div>
              <div style="display:flex;align-items:flex-start;gap:16px;padding:2px 10px 12px">
                <span style="font-family:'Bellinzo',serif;font-weight:700;font-size:56px;line-height:.9;color:#000000">{{ $p['big'] }}</span>
                <span style="font-size:14px;color:#474747;line-height:1.5;padding-top:6px">{{ $p['label'] }}</span>
              </div>
            </div>
          @endforeach
        </div>
      </div>
    </section>

    <!-- SERVICES -->
    <section id="servicios" style="max-width:1320px;margin:0 auto;padding:110px 36px 70px">
      <div style="display:flex;justify-content:space-between;align-items:flex-end;flex-wrap:wrap;gap:16px;margin-bottom:52px">
        <div>
          <span style="font-size:12px;letter-spacing:.34em;text-transform:uppercase;color:#5C5C5C">{{ $t['servicesKicker'] }}</span>
          <h2 style="font-family:'Bellinzo',serif;font-weight:700;font-size:clamp(38px,5vw,68px);line-height:1;letter-spacing:-.02em;margin-top:16px">{{ $t['servicesTitle1'] }}<br>{{ $t['servicesTitle2'] }}</h2>
        </div>
        <p style="font-family:'Miller Text Italic',serif;font-style:italic;font-size:21px;color:#474747;max-width:32ch">{{ $t['servicesIntro'] }}</p>
      </div>
      <div data-grid="services" style="display:grid;grid-template-columns:repeat(4,minmax(0,1fr));grid-auto-rows:225px;grid-auto-flow:dense;gap:14px">
        <div id="specialization-1" data-link="{{ $performanceUrl }}" role="link" tabindex="0" style="grid-column:span 2;grid-row:span 2;min-width:0;background:#2D48F2;color:#FFFFFF;border-radius:24px;padding:28px;display:flex;flex-direction:column;cursor:pointer;transition:transform .25s" class="prototype-hover-7">
          <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:12px">
            <h3 style="font-family:'Bellinzo',serif;font-weight:700;font-size:clamp(24px,2.2vw,34px);line-height:1.02;min-width:0;overflow-wrap:break-word">{{ $t['services'][0]['title'] }}</h3>
            <span style="font-size:12px;letter-spacing:.14em;opacity:.7;flex-shrink:0">01</span>
          </div>
          <p style="font-size:15px;line-height:1.5;opacity:.85;margin-top:14px;max-width:34ch">{{ $t['services'][0]['desc'] }}</p>
          <div style="margin-top:auto;display:flex;justify-content:space-between;align-items:flex-end;gap:12px">
            <a href="{{ $performanceUrl }}" style="background:#FFFFFF;color:#2D48F2;border:none;cursor:pointer;font-family:'Space Grotesk',sans-serif;font-weight:600;font-size:14px;padding:12px 22px;border-radius:100px" class="prototype-hover-4">{{ $t['servicesBtn'] }}</a>
            <svg width="76" height="76" viewBox="0 0 24 24" fill="none"><circle cx="5" cy="19" r="2.8" fill="#FFFFFF"/><circle cx="19" cy="5" r="2.8" fill="#FFFFFF"/><path d="M7.6 16.4C10.5 15 13.5 9 16.4 7.6" stroke="#FFFFFF" stroke-width="2.2" stroke-linecap="round"/></svg>
          </div>
        </div>
        <div id="specialization-2" style="grid-column:span 2;min-width:0;background:#FB6C26;color:#000000;border-radius:24px;padding:28px;display:flex;flex-direction:column;transition:transform .25s" class="prototype-hover-7">
          <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:12px">
            <h3 style="font-family:'Bellinzo',serif;font-weight:700;font-size:clamp(22px,1.9vw,28px);line-height:1.02;min-width:0;overflow-wrap:break-word">{{ $t['services'][1]['title'] }}</h3>
            <span style="font-size:12px;letter-spacing:.14em;opacity:.7;flex-shrink:0">02</span>
          </div>
          <p style="font-size:14px;line-height:1.45;opacity:.8;margin-top:10px;max-width:38ch">{{ $t['services'][1]['desc'] }}</p>
          <div style="margin-top:auto;display:flex;justify-content:space-between;align-items:flex-end;gap:12px">
            <a href="{{ $serviceUrls['brand'] }}" style="background:#000000;color:#FFFFFF;border:none;cursor:pointer;font-family:'Space Grotesk',sans-serif;font-weight:600;font-size:14px;padding:12px 22px;border-radius:100px" class="prototype-hover-4">{{ $t['servicesBtn'] }}</a>
            <span style="font-family:'Bellinzo',serif;font-weight:700;font-size:88px;line-height:.45">”</span>
          </div>
        </div>
        <div id="specialization-3" style="grid-row:span 2;min-width:0;background:#15F7A8;color:#000000;border-radius:24px;padding:28px;display:flex;flex-direction:column;transition:transform .25s" class="prototype-hover-7">
          <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:12px">
            <h3 style="font-family:'Bellinzo',serif;font-weight:700;font-size:20px;line-height:1.06;min-width:0">{{ $t['services'][2]['title'] }}</h3>
            <span style="font-size:12px;letter-spacing:.14em;opacity:.7;flex-shrink:0">03</span>
          </div>
          <p style="font-size:14px;line-height:1.45;opacity:.8;margin-top:10px">{{ $t['services'][2]['desc'] }}</p>
          <svg style="margin-top:auto;align-self:flex-end" width="56" height="56" viewBox="0 0 24 24" fill="none"><path d="M5 3l14 8-6 2-2 6-6-16z" fill="#000000"/></svg>
          <a href="{{ $serviceUrls['experience'] }}" style="margin-top:14px;background:#000000;color:#FFFFFF;border:none;cursor:pointer;font-family:'Space Grotesk',sans-serif;font-weight:600;font-size:14px;padding:12px 22px;border-radius:100px;align-self:flex-start" class="prototype-hover-4">{{ $t['servicesBtn'] }}</a>
        </div>
        <div id="specialization-4" style="grid-row:span 2;min-width:0;background:#FDDB02;color:#000000;border-radius:24px;padding:28px;display:flex;flex-direction:column;transition:transform .25s" class="prototype-hover-7">
          <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:12px">
            <h3 style="font-family:'Bellinzo',serif;font-weight:700;font-size:20px;line-height:1.06;min-width:0">{{ $t['services'][3]['title'] }}</h3>
            <span style="font-size:12px;letter-spacing:.14em;opacity:.7;flex-shrink:0">04</span>
          </div>
          <span style="font-size:12px;letter-spacing:.14em;text-transform:uppercase;opacity:.75;margin-top:8px">{{ $t['services'][3]['kind'] }}</span>
          <p style="font-size:14px;line-height:1.45;opacity:.8;margin-top:10px">{{ $t['services'][3]['desc'] }}</p>
          <svg style="margin-top:auto;align-self:flex-end" width="52" height="52" viewBox="0 0 24 24" fill="none"><path d="M8 6L2 12L8 18M16 6L22 12L16 18M14 3L10 21" stroke="#000000" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
          <a href="{{ $serviceUrls['makitha'] }}" style="margin-top:14px;background:#000000;color:#FFFFFF;border:none;cursor:pointer;font-family:'Space Grotesk',sans-serif;font-weight:600;font-size:14px;padding:12px 22px;border-radius:100px;align-self:flex-start" class="prototype-hover-4">{{ $t['servicesBtn'] }}</a>
        </div>
        <div id="specialization-5" style="grid-column:span 2;min-width:0;background:#D48BFD;color:#000000;border-radius:24px;padding:28px;display:flex;flex-direction:column;transition:transform .25s" class="prototype-hover-7">
          <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:12px">
            <h3 style="font-family:'Bellinzo',serif;font-weight:700;font-size:clamp(22px,1.9vw,28px);line-height:1.02;min-width:0;overflow-wrap:break-word">{{ $t['services'][4]['title'] }}</h3>
            <span style="font-size:12px;letter-spacing:.14em;opacity:.7;flex-shrink:0">05</span>
          </div>
          <p style="font-size:14px;line-height:1.45;opacity:.8;margin-top:10px;max-width:38ch">{{ $t['services'][4]['desc'] }}</p>
          <div style="margin-top:auto;display:flex;justify-content:space-between;align-items:flex-end;gap:12px">
            <a href="{{ $serviceUrls['social'] }}" style="background:#000000;color:#FFFFFF;border:none;cursor:pointer;font-family:'Space Grotesk',sans-serif;font-weight:600;font-size:14px;padding:12px 22px;border-radius:100px" class="prototype-hover-4">{{ $t['servicesBtn'] }}</a>
            <svg width="52" height="52" viewBox="0 0 24 24" fill="none"><path d="M4 5a2 2 0 012-2h12a2 2 0 012 2v9a2 2 0 01-2 2H9l-5 5V5z" fill="#000000"/></svg>
          </div>
        </div>
      </div>
      <div style="display:flex;justify-content:center;gap:14px;margin-top:44px">
        <a href="{{ $contactUrl }}" style="background:#FB6C26;color:#000000;border:none;cursor:pointer;font-family:'Space Grotesk',sans-serif;font-weight:600;font-size:15px;padding:16px 30px;border-radius:100px" class="prototype-hover-2">{{ $t['hero']['card2Btn'] }}</a>
        <a href="{{ $contactUrl }}" style="background:transparent;color:#000000;border:1px solid rgba(0,0,0,.35);cursor:pointer;font-family:'Space Grotesk',sans-serif;font-weight:600;font-size:15px;padding:16px 30px;border-radius:100px" class="prototype-hover-3">{{ $t['cta'] }}</a>
      </div>
    </section>

    <!-- CASES -->
    <section id="casos" style="max-width:1320px;margin:0 auto;padding:70px 36px">
      <div style="margin-bottom:52px">
        <span style="font-size:12px;letter-spacing:.34em;text-transform:uppercase;color:#5C5C5C">{{ $t['casesKicker'] }}</span>
        <h2 style="font-family:'Bellinzo',serif;font-weight:300;font-size:clamp(38px,5vw,68px);line-height:1;margin-top:16px">{{ $t['casesTitle'] }}</h2>
        <a class="home-section-link" href="{{ $agencyUrls['cases'] }}">{{ $locale === 'es' ? 'Explorar casos' : 'Explore work' }} ↗</a>
      </div>
      <div style="position:relative;border-radius:24px;overflow:hidden;aspect-ratio:16/7;background:linear-gradient(140deg,#141414,#000000 70%);border:1px solid rgba(0,0,0,.14);margin-bottom:52px">
        <div style="position:absolute;inset:0;background:radial-gradient(ellipse 60% 80% at 50% 100%,rgba(251,108,38,.16),transparent 65%)"></div>
        <div style="position:absolute;inset:0;display:flex;align-items:center;justify-content:center;flex-direction:column;gap:16px">
          <div style="width:76px;height:76px;border-radius:50%;background:#FFFFFF;display:flex;align-items:center;justify-content:center" class="prototype-hover-6">
            <svg width="22" height="24" viewBox="0 0 22 24" fill="none"><path d="M21 12L0 24V0l21 12z" fill="#000000"/></svg>
          </div>
          <span style="font-size:12px;color:#9E9E9E;letter-spacing:.22em;text-transform:uppercase">{{ $t['hero']['videoNote'] }}</span>
        </div>
      </div>
      <div data-grid="cases" style="display:grid;grid-template-columns:repeat(3,1fr);gap:14px">
        @foreach ($t['cases'] as $c)
          <div style="border-radius:18px;padding:30px;display:flex;flex-direction:column;min-height:300px;background:#FFFFFF;border:1px solid rgba(0,0,0,.1);box-shadow:0 16px 40px rgba(0,0,0,.08)" class="prototype-hover-8">
            <div style="display:flex;justify-content:space-between;align-items:center">
              <span style="font-family:'Bellinzo',serif;font-size:18px">{{ $c['client'] }}</span>
              <span style="font-size:10px;letter-spacing:.12em;text-transform:uppercase;color:#5C5C5C;border:1px dashed rgba(0,0,0,.3);border-radius:100px;padding:3px 9px">{{ $t['demoTag'] }}</span>
            </div>
            <div style="margin-top:auto">
              <div style="font-family:'Bellinzo',serif;font-weight:300;font-size:clamp(52px,6.4vw,80px);line-height:1">{{ $c['metric'] }}</div>
              <div style="font-size:14px;color:#5C5C5C;margin-top:6px">{{ $c['metricLabel'] }}</div>
            </div>
            <p style="font-size:14px;color:#474747;line-height:1.5;margin-top:20px">{{ $c['desc'] }}</p>
          </div>
        @endforeach
      </div>
    </section>

    <!-- COLLABORATION NARRATIVE -->
    <section id="nosotros" style="position:relative;background:#000000;color:#FFFFFF;overflow:hidden">
      <div style="position:absolute;inset:0;background:radial-gradient(ellipse 60% 70% at 80% 100%,rgba(251,108,38,.15),transparent 60%);pointer-events:none"></div>
      <div data-grid="collaboration" style="position:relative;max-width:1320px;margin:0 auto;padding:clamp(80px,10vw,140px) 36px;display:grid;grid-template-columns:1.15fr .85fr;gap:clamp(40px,6vw,90px);align-items:center">
        <div>
          <span style="font-size:12px;letter-spacing:.34em;text-transform:uppercase;color:#8A8A8A">{{ $t['collabKicker'] }}</span>
          <blockquote style="font-family:'Miller Text Italic',serif;font-style:italic;font-size:clamp(32px,4.8vw,64px);line-height:1.1;letter-spacing:-.015em;text-wrap:balance;margin:26px 0 0">“{{ $t['collabQuote'] }}”</blockquote>
          <p style="font-size:17px;color:#9E9E9E;max-width:46ch;margin-top:30px;line-height:1.6">{{ $t['collabBody'] }}</p>
          <a class="home-section-link home-section-link-light" href="{{ $agencyUrls['about'] }}">{{ $locale === 'es' ? 'Conoce a Feat.' : 'Meet Feat.' }} ↗</a>
        </div>
        <div>
          <h3 style="font-family:'Bellinzo',serif;font-weight:300;font-size:16px;margin-bottom:18px">{{ $t['clientsTitle'] }}</h3>
          <div style="display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:10px">
            @foreach ($t['clients'] as $cl)
              <div style="aspect-ratio:16/9;display:flex;align-items:center;justify-content:center;padding:16px">
                <div class="client-placeholder" role="img" aria-label="{{ $cl }}">{{ $cl }}</div>
              </div>
            @endforeach
          </div>
          <p style="font-size:12px;color:#8A8A8A;margin-top:12px">{{ $t['clientsNote'] }}</p>
        </div>
      </div>
    </section>

    <!-- MARKET SELECTOR BLOCK -->
    <section style="border-top:1px solid rgba(0,0,0,.14)">
      <div data-grid="two" style="max-width:1320px;margin:0 auto;padding:92px 36px;display:grid;grid-template-columns:1fr 1fr;gap:52px;align-items:center">
        <div>
          <span style="font-size:12px;letter-spacing:.34em;text-transform:uppercase;color:#5C5C5C">{{ $t['marketKicker'] }}</span>
          <h2 style="font-family:'Bellinzo',serif;font-weight:300;font-size:clamp(32px,4.2vw,56px);line-height:1.02;margin-top:16px">{{ $t['marketTitle'] }}</h2>
          <p style="font-size:17px;color:#474747;line-height:1.6;margin-top:22px;max-width:44ch">{{ $t['marketBody'] }}</p>
        </div>
        <div style="display:flex;flex-direction:column;gap:12px">
          @foreach ($t['offices'] as $o)
            <div data-office="{{ $o['mk'] }}" role="button" tabindex="0" aria-expanded="{{ $o['mk'] === $market ? 'true' : 'false' }}" class="office-card {{ $o['mk'] === $market ? 'is-active' : '' }}">
              <div style="display:flex;justify-content:space-between;align-items:center">
                <div style="display:flex;align-items:center;gap:16px">
                  <span style="width:52px;height:52px;flex-shrink:0;background:url('{{ asset('assets/ill-'.$o['mk'].'.svg') }}') center/contain no-repeat"></span>
                  <div style="display:flex;align-items:baseline;gap:12px">
                    <span style="font-family:'Bellinzo',serif;font-size:23px">{{ $o['city'] }}</span>
                    <span style="font-size:13px;color:#5C5C5C">{{ $o['tag'] }}</span>
                  </div>
                </div>
                <span style="font-size:13px;color:{{ $o['mk'] === $market ? '#FB6C26' : '#938B77' }}"><span data-office-sign>{{ $o['mk'] === $market ? '– ' : '+ ' }}</span>{{ $o['code'] }}</span>
              </div>
              <div class="office-details" data-office-details {{ $o['mk'] === $market ? '' : 'hidden' }}>
                <p style="font-family:'Miller Text Italic',serif;font-style:italic;font-size:17px;line-height:1.45;color:#333333;margin-top:16px">{{ $o['message'] }}</p>
                <div style="display:flex;flex-wrap:wrap;gap:8px 28px;margin-top:16px">
                  <div style="display:flex;flex-direction:column;gap:2px">
                    <span style="font-size:11px;letter-spacing:.14em;text-transform:uppercase;color:#5C5C5C">{{ $t['addrLabel'] }}</span>
                    <span style="font-size:14px;color:#474747">{{ $o['address'] }}</span>
                  </div>
                  <div style="display:flex;flex-direction:column;gap:2px">
                    <span style="font-size:11px;letter-spacing:.14em;text-transform:uppercase;color:#5C5C5C">{{ $t['phoneLabel'] }}</span>
                    <span style="font-size:14px;color:#474747">{{ $o['phone'] }}</span>
                  </div>
                </div>
              </div>
            </div>
          @endforeach
        </div>
      </div>
    </section>
  </main>
@endsection
