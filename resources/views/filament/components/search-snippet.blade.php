@php
    $titleLength = mb_strlen($title);
    $descriptionLength = mb_strlen($description);
    $address = preg_replace('~^https?://~', '', rtrim($url, '/'));
@endphp
<div class="cms-snippet">
    <p class="cms-snippet-label">Vista previa en Google</p>
    <div class="cms-snippet-card">
        <div class="cms-snippet-site"><span class="cms-snippet-favicon" aria-hidden="true">f.</span><div><strong>Feat.</strong><span>{{ str_replace('/', ' › ', $address) }}</span></div></div>
        <p class="cms-snippet-title">{{ $titleLength ? \Illuminate\Support\Str::limit($title, 60) : 'Escribe un título SEO' }}</p>
        <p class="cms-snippet-description">{{ $descriptionLength ? \Illuminate\Support\Str::limit($description, 155) : 'Escribe una descripción que resuma la página.' }}</p>
    </div>
    <div class="cms-snippet-meters">
        <span @class(['is-over' => $titleLength > 60])>Título: {{ $titleLength }}/60</span>
        <span @class(['is-over' => $descriptionLength > 155])>Descripción: {{ $descriptionLength }}/155</span>
    </div>
</div>
<style>
    .cms-snippet{display:grid;gap:8px}.cms-snippet-label{font-size:12px;font-weight:600;opacity:.7}
    .cms-snippet-card{background:#fff;color:#202124;border-radius:12px;padding:16px 18px;border:1px solid #e5e7eb;font-family:Arial,sans-serif;max-width:640px}
    .cms-snippet-site{display:flex;gap:10px;align-items:center;margin-bottom:6px}.cms-snippet-site div{display:grid;line-height:1.3}
    .cms-snippet-site strong{font-size:14px;font-weight:400}.cms-snippet-site span{font-size:12px;color:#4d5156;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;max-width:520px}
    .cms-snippet-favicon{display:grid;place-items:center;width:28px;height:28px;border-radius:50%;background:#f1f3f4;font-weight:700;font-size:12px}
    .cms-snippet-title{color:#1a0dab;font-size:20px;line-height:1.3;margin:2px 0 4px}.cms-snippet-description{font-size:14px;line-height:1.58;color:#4d5156}
    .cms-snippet-meters{display:flex;gap:16px;font-size:12px;opacity:.75}.cms-snippet-meters .is-over{color:#d97706;opacity:1;font-weight:600}
</style>
