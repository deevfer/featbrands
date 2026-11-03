@extends('site.layout')

@section('content')
<main id="main-content">
    <section style="position:relative;overflow:hidden">
      <div style="position:absolute;inset:0;background:radial-gradient(ellipse 60% 50% at 85% 0%,rgba(251,108,38,.14),transparent 60%);pointer-events:none"></div>
      <div style="position:relative;max-width:1040px;margin:0 auto;padding:56px 36px 0">
        <div style="display:flex;gap:8px;font-size:13px;color:#5C5C5C">
          <a href="{{ $homeUrl }}" style="background:none;border:none;color:#5C5C5C;cursor:pointer;font-family:inherit;font-size:13px;padding:0" class="prototype-hover-9">Feat.</a>
          <span>/</span><a href="{{ $homeUrl }}" style="background:none;border:none;color:#5C5C5C;cursor:pointer;font-family:inherit;font-size:13px;padding:0" class="prototype-hover-9">{{ $p['crumbServices'] }}</a>
          <span>/</span><span style="color:#000000">{{ $p['crumbCurrent'] }}</span>
        </div>
      </div>
      <div style="position:relative;max-width:1040px;margin:0 auto;padding:30px 36px 40px">
        <span style="font-size:12px;letter-spacing:.34em;text-transform:uppercase;color:#FB6C26">{{ $p['kicker'] }}</span>
        <h1 style="font-family:'Bellinzo',serif;font-weight:300;font-size:clamp(42px,6.4vw,92px);line-height:.98;letter-spacing:-.025em;margin-top:20px;max-width:16ch;text-wrap:balance">{{ $p['h1'] }}</h1>
        <div style="margin-top:38px;border:1px solid #FB6C26;border-radius:16px;padding:26px 30px;background:rgba(251,108,38,.06);display:flex;gap:18px">
          <span style="font-size:11px;letter-spacing:.18em;text-transform:uppercase;color:#FB6C26;white-space:nowrap;padding-top:3px">{{ $p['answerTag'] }}</span>
          <p style="font-size:clamp(17px,1.9vw,21px);line-height:1.55;color:#000000">{{ $p['answer'] }}</p>
        </div>
      </div>
    </section>

    <section style="max-width:1040px;margin:0 auto;padding:40px 36px;border-top:1px solid rgba(0,0,0,.14)">
      <div data-grid="performance" style="display:grid;grid-template-columns:200px 1fr;gap:32px">
        <h2 style="font-size:13px;letter-spacing:.22em;text-transform:uppercase;color:#5C5C5C">{{ $p['problemH'] }}</h2>
        <p style="font-family:'Miller Text Italic',serif;font-style:italic;font-size:clamp(22px,2.7vw,32px);line-height:1.32;color:#333333">{{ $p['problem'] }}</p>
      </div>
    </section>

    <section style="max-width:1040px;margin:0 auto;padding:40px 36px;border-top:1px solid rgba(0,0,0,.14)">
      <div data-grid="performance" style="display:grid;grid-template-columns:200px 1fr;gap:32px">
        <h2 style="font-size:13px;letter-spacing:.22em;text-transform:uppercase;color:#5C5C5C">{{ $p['capsH'] }}</h2>
        <div style="display:flex;flex-direction:column">
          @foreach ($p['caps'] as $cap)
            <div style="display:flex;gap:20px;padding:20px 0;border-bottom:1px solid rgba(0,0,0,.14);align-items:baseline">
              <span style="font-family:'Bellinzo',serif;font-weight:700;font-size:22px;color:#FB6C26;min-width:36px">{{ $cap['n'] }}</span>
              <div>
                <div style="font-size:19px;font-weight:500">{{ $cap['title'] }}</div>
                <div style="font-size:15px;color:#5C5C5C;margin-top:4px">{{ $cap['desc'] }}</div>
              </div>
            </div>
          @endforeach
        </div>
      </div>
    </section>

    <section style="max-width:1040px;margin:0 auto;padding:40px 36px;border-top:1px solid rgba(0,0,0,.14)">
      <div data-grid="performance" style="display:grid;grid-template-columns:200px 1fr;gap:32px">
        <h2 style="font-size:13px;letter-spacing:.22em;text-transform:uppercase;color:#5C5C5C">{{ $p['processH'] }}</h2>
        <div data-grid="two" style="display:grid;grid-template-columns:1fr 1fr;gap:14px">
          @foreach ($p['process'] as $st)
            <div style="border:1px solid rgba(0,0,0,.12);border-radius:16px;padding:24px;background:#FFFFFF" class="prototype-hover-10">
              <div style="font-family:'Bellinzo',serif;font-weight:700;font-size:32px;color:#FB6C26">{{ $st['n'] }}</div>
              <div style="font-size:17px;font-weight:500;margin-top:10px">{{ $st['title'] }}</div>
              <div style="font-size:14px;color:#5C5C5C;margin-top:6px;line-height:1.5">{{ $st['desc'] }}</div>
            </div>
          @endforeach
        </div>
      </div>
    </section>

    <section style="max-width:1040px;margin:0 auto;padding:40px 36px;border-top:1px solid rgba(0,0,0,.14)">
      <h2 style="font-size:13px;letter-spacing:.22em;text-transform:uppercase;color:#5C5C5C;margin-bottom:24px">{{ $p['casesH'] }}</h2>
      <div data-grid="two" style="display:grid;grid-template-columns:1fr 1fr;gap:14px">
        @foreach ($p['cases'] as $c)
          <div style="border:1px solid rgba(0,0,0,.1);border-radius:18px;padding:28px;background:#FFFFFF;box-shadow:0 16px 40px rgba(0,0,0,.08)" class="prototype-hover-10">
            <div style="display:flex;justify-content:space-between;align-items:center">
              <span style="font-family:'Bellinzo',serif;font-size:18px">{{ $c['client'] }}</span>
              <span style="font-size:10px;letter-spacing:.12em;text-transform:uppercase;color:#5C5C5C;border:1px dashed rgba(0,0,0,.3);border-radius:100px;padding:3px 9px">{{ $t['demoTag'] }}</span>
            </div>
            <div style="font-family:'Bellinzo',serif;font-weight:300;font-size:60px;line-height:1;margin-top:22px">{{ $c['metric'] }}</div>
            <div style="font-size:14px;color:#5C5C5C;margin-top:6px">{{ $c['metricLabel'] }}</div>
            <p style="font-size:14px;color:#474747;line-height:1.5;margin-top:14px">{{ $c['desc'] }}</p>
          </div>
        @endforeach
      </div>
    </section>

    <!-- FAQ (FAQPage schema) -->
    <section style="max-width:1040px;margin:0 auto;padding:40px 36px;border-top:1px solid rgba(0,0,0,.14)">
      <div data-grid="performance" style="display:grid;grid-template-columns:200px 1fr;gap:32px">
        <h2 style="font-size:13px;letter-spacing:.22em;text-transform:uppercase;color:#5C5C5C">{{ $p['faqH'] }}</h2>
        <div style="display:flex;flex-direction:column">
          @foreach ($p['faq'] as $f)
            <div style="border-bottom:1px solid rgba(0,0,0,.14)">
              <button type="button" data-faq-toggle aria-controls="faq-answer-{{ $loop->index }}" aria-expanded="{{ $loop->first ? 'true' : 'false' }}" style="width:100%;background:none;border:none;cursor:pointer;color:#000000;font-family:'Space Grotesk',sans-serif;text-align:left;font-size:18px;font-weight:500;padding:22px 0;display:flex;justify-content:space-between;gap:20px;align-items:center">
                <span>{{ $f['q'] }}</span>
                <span style="color:#FB6C26;font-size:22px;flex-shrink:0"><span data-faq-sign>{{ $loop->first ? '–' : '+' }}</span></span>
              </button>
              <div data-faq-answer {{ $loop->first ? '' : 'hidden' }} id="faq-answer-{{ $loop->index }}">
                <p style="font-size:16px;color:#474747;line-height:1.6;padding-bottom:22px;max-width:60ch">{{ $f['a'] }}</p>
              </div>
            </div>
          @endforeach
        </div>
      </div>
    </section>

    <!-- CTA -->
    <section style="max-width:1320px;margin:0 auto;padding:60px 36px 100px">
      <div style="position:relative;border-radius:22px;overflow:hidden;background:#000000;color:#FFFFFF;padding:clamp(44px,6.5vw,80px);display:flex;flex-direction:column;align-items:flex-start;gap:24px">
        <h2 style="font-family:'Bellinzo',serif;font-weight:400;font-size:clamp(34px,4.8vw,62px);line-height:1;max-width:18ch;letter-spacing:-.02em">{{ $p['ctaTitle'] }}</h2>
        <p style="font-size:18px;max-width:48ch;line-height:1.55;color:#BDBDBD">{{ $p['ctaBody'] }}</p>
        <a href="{{ $p['ctaHref'] ?? $contactUrl }}" style="background:#FB6C26;color:#000000;border:none;cursor:pointer;font-family:'Space Grotesk',sans-serif;font-weight:600;font-size:16px;padding:17px 36px;border-radius:100px" class="prototype-hover-2">{{ $p['ctaBtn'] }}</a>
      </div>
      <div data-grid="two" style="display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-top:14px">
        @foreach ($p['related'] as $r)
          <div data-link="{{ $r['href'] ?? $serviceUrls[$r['title'] === 'Experience & Tech' ? 'experience' : 'social'] }}" role="link" tabindex="0" style="border-radius:22px;padding:30px 34px;background:{{ $r['bg'] }};color:#000000;cursor:pointer;display:flex;flex-direction:column;gap:10px" class="prototype-hover-11">
            <span style="font-size:11px;letter-spacing:.22em;text-transform:uppercase;color:rgba(0,0,0,.6)">{{ $r['tag'] }}</span>
            <span style="font-family:'Bellinzo',serif;font-size:26px;letter-spacing:-.01em">{{ $r['title'] }}</span>
            <span style="font-size:14px;color:rgba(0,0,0,.7);line-height:1.5">{{ $r['desc'] }}</span>
            <span style="font-size:14px;font-weight:600;color:#000000;margin-top:6px">{{ $r['cta'] }} →</span>
          </div>
        @endforeach
      </div>
    </section>
  </main>
@endsection
