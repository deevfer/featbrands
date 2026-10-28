<footer id="contacto" style="border-top:1px solid rgba(0,0,0,.14);background:#000000;color:#FFFFFF">
    <div style="max-width:1320px;margin:0 auto;padding:70px 36px 44px">
      <div data-grid="footer" style="display:grid;grid-template-columns:1.4fr 1fr 1fr 1fr;gap:32px">
        <div>
          <img src="{{ asset('assets/feat-white.svg') }}" alt="Feat." style="height:48px;display:block;margin-bottom:22px"/>
          <p style="font-family:'Miller Text Italic',serif;font-style:italic;font-size:20px;color:#9E9E9E;max-width:26ch;line-height:1.4">{{ $t['footer']['tagline'] }}</p>
        </div>
        @foreach ($t['footer']['cols'] as $col)
          <div>
            <div style="font-size:11px;letter-spacing:.22em;text-transform:uppercase;color:#5C5C5C;margin-bottom:18px">{{ $col['head'] }}</div>
            <div style="display:flex;flex-direction:column;gap:10px">
              @foreach ($col['items'] as $li)
                <a href="{{ in_array($col['head'], ['Servicios', 'Services'], true) ? $serviceLinks[$loop->index]['href'] : (in_array($col['head'], ['Agencia', 'Agency'], true) ? $footerAgencyUrls[$loop->index] : $contactUrl) }}" style="font-size:15px;color:#9E9E9E;cursor:pointer" class="prototype-hover-12">{{ $li }}</a>
              @endforeach
            </div>
          </div>
        @endforeach
      </div>
      <div style="display:flex;justify-content:space-between;flex-wrap:wrap;gap:12px;margin-top:60px;padding-top:26px;border-top:1px solid rgba(255,255,255,.1);font-size:13px;color:#5C5C5C">
        <span>{{ $t['footer']['legal'] }}</span>
        <span>{{ $t['footer']['offices'] }}</span>
      </div>
    </div>
  </footer>
