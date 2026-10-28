<header class="site-header" style="position:sticky;top:16px;z-index:50">
    <div style="max-width:1320px;margin:0 auto;padding:0 36px">
    <div style="display:flex;align-items:center;gap:14px;background:rgba(0,0,0,.9);backdrop-filter:blur(18px);border-radius:100px;border:1px solid rgba(255,255,255,.1);box-shadow:0 14px 40px rgba(0,0,0,.3);padding:8px 10px 8px 18px" class="header-bar">
      <a href="{{ $homeUrl }}" style="background:none;border:none;cursor:pointer;padding:0;display:flex;align-items:center;flex-shrink:0">
        <img src="{{ asset('assets/feat-white.svg') }}" alt="Feat." style="height:32px;display:block"/>
      </a>
      <nav class="site-nav" aria-label="{{ $locale === 'es' ? 'Navegación principal' : 'Main navigation' }}" style="display:flex;gap:16px;margin-left:6px;min-width:0;align-items:center">
        <div style="position:relative" data-services-menu>
          <button data-services-toggle aria-expanded="false" style="background:none;border:none;cursor:pointer;font-family:'Space Grotesk',sans-serif;font-size:12px;font-weight:500;letter-spacing:.02em;text-transform:uppercase;color:#FFFFFF;opacity:.6;padding:4px 0;white-space:nowrap;display:flex;align-items:center;gap:5px" class="prototype-hover-0 {{ in_array($page, array_column($serviceLinks, 'route'), true) ? 'is-active' : '' }}">{{ $t['nav'][0] }}<span style="font-size:9px">▾</span></button>
          <div class="services-dropdown" data-services-dropdown hidden>
            <div style="background:rgba(0,0,0,.96);backdrop-filter:blur(18px);border:1px solid rgba(255,255,255,.12);border-radius:16px;padding:8px;display:flex;flex-direction:column;gap:2px;min-width:250px;box-shadow:0 18px 44px rgba(0,0,0,.4)">
            @foreach ($serviceLinks as $s)
              <a href="{{ $s['href'] }}" @if ($page === $s['route']) aria-current="page" @endif style="background:none;border:none;cursor:pointer;font-family:'Space Grotesk',sans-serif;font-size:13px;font-weight:500;color:#FFFFFF;opacity:.75;padding:9px 14px;border-radius:10px;text-align:left;display:flex;align-items:center;gap:10px;white-space:nowrap" class="prototype-hover-1 {{ $page === $s['route'] ? 'is-active' : '' }}"><span style="width:8px;height:8px;border-radius:50%;flex-shrink:0;background:{{ $s['color'] }}"></span>{{ $s['title'] }}</a>
            @endforeach
            </div>
          </div>
        </div>
        @foreach ($navRest as $item)
          <a data-nav-page="{{ $item['route'] ?? '' }}" href="{{ $item['href'] }}" @if ($page === $item['route']) aria-current="page" @endif style="background:none;border:none;cursor:pointer;font-family:'Space Grotesk',sans-serif;font-size:12px;font-weight:500;letter-spacing:.02em;text-transform:uppercase;color:#FFFFFF;opacity:.6;padding:4px 0;white-space:nowrap" class="prototype-hover-0 {{ $page === $item['route'] ? 'is-active' : '' }}">{{ $item['label'] }}</a>
        @endforeach
      </nav>
      <div style="margin-left:auto;display:flex;align-items:center;gap:8px;flex-shrink:0" class="header-controls">
        <div data-language-switcher role="group" aria-label="{{ $locale === 'es' ? 'Idioma' : 'Language' }}" style="display:flex;border:1px solid rgba(255,255,255,.2);border-radius:100px;overflow:hidden">
          <a href="{{ $languageUrls['es'] }}" data-language="es" class="language-choice {{ $locale === 'es' ? 'is-active' : '' }}" aria-label="Español">ES</a>
          <a href="{{ $languageUrls['en'] }}" data-language="en" class="language-choice {{ $locale === 'en' ? 'is-active' : '' }}" aria-label="English">EN</a>
        </div>
        <div style="display:flex;gap:3px;align-items:center">
          @foreach ($markets as $m)
            <button type="button" data-market-choice="{{ strtolower($m['code']) }}" class="market-choice {{ strtolower($m['code']) === $market ? 'is-active' : '' }}" aria-pressed="{{ strtolower($m['code']) === $market ? 'true' : 'false' }}" title="{{ $m['name'] }}">{{ $m['code'] }}</button>
          @endforeach
        </div>
        <a href="{{ $contactUrl }}" style="background:#FDDB02;color:#000000;border:none;cursor:pointer;font-family:'Space Grotesk',sans-serif;font-weight:600;font-size:13px;padding:9px 16px;border-radius:100px;white-space:nowrap" class="prototype-hover-2">{{ $t['cta'] }}</a>
      </div>
    </div>
    </div>
  </header>
