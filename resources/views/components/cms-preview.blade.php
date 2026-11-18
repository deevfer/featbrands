@props(['config'])

<link rel="stylesheet" href="{{ asset('css/cms-preview.css').'?v='.filemtime(public_path('css/cms-preview.css')) }}">
<script src="{{ asset('js/cms-preview.js').'?v='.filemtime(public_path('js/cms-preview.js')) }}"></script>

<div class="cms-live" x-data="cmsPreview(@js($config))" x-bind:class="{ 'is-collapsed': collapsed, 'is-overlay-open': overlayOpen }" x-on:keydown.escape.window="overlayOpen = false">
    <div class="cms-live-form" x-ref="form">
        {{ $slot }}
    </div>

    <aside class="cms-live-panel" wire:ignore aria-label="Vista previa de la página">
        <div class="cms-live-toolbar">
            <div class="cms-live-address">
                <span class="cms-live-dot" x-bind:data-status="status" aria-hidden="true"></span>
                <span class="cms-live-path" x-text="path || 'Cargando…'"></span>
                <span class="cms-live-badge" x-show="dirty" x-cloak>Sin guardar</span>
            </div>
            <div class="cms-live-controls">
                <div class="cms-live-segment" role="group" aria-label="Dispositivo">
                    <button type="button" x-on:click="setDevice('desktop')" x-bind:aria-pressed="device === 'desktop'" title="Escritorio" aria-label="Escritorio"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><rect x="3" y="4" width="18" height="12" rx="1.5"/><path d="M8 20h8M12 16v4"/></svg></button>
                    <button type="button" x-on:click="setDevice('tablet')" x-bind:aria-pressed="device === 'tablet'" title="Tablet" aria-label="Tablet"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><rect x="5" y="3" width="14" height="18" rx="2"/><path d="M11 18h2"/></svg></button>
                    <button type="button" x-on:click="setDevice('mobile')" x-bind:aria-pressed="device === 'mobile'" title="Celular" aria-label="Celular"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><rect x="7" y="3" width="10" height="18" rx="2"/><path d="M11 18h2"/></svg></button>
                </div>
                <div class="cms-live-segment" role="group" aria-label="Idioma" x-show="config.localized">
                    <button type="button" x-on:click="setLocale('es')" x-bind:aria-pressed="locale === 'es'">ES</button>
                    <button type="button" x-on:click="setLocale('en')" x-bind:aria-pressed="locale === 'en'">EN</button>
                </div>
                <button type="button" class="cms-live-icon" x-on:click="refresh()" title="Actualizar vista previa" aria-label="Actualizar vista previa"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true" x-bind:class="{ 'is-spinning': status === 'loading' }"><path d="M20 12a8 8 0 1 1-2.3-5.6M20 4v4h-4"/></svg></button>
                <a class="cms-live-icon" x-bind:href="config.publicUrl" target="_blank" rel="noopener" title="Abrir la página publicada" aria-label="Abrir la página publicada"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M14 4h6v6M20 4l-9 9M18 14v5a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1h5"/></svg></a>
                <button type="button" class="cms-live-icon cms-live-hide" x-on:click="toggleCollapsed()" title="Ocultar vista previa" aria-label="Ocultar vista previa"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M13 6l6 6-6 6M5 6l6 6-6 6"/></svg></button>
                <button type="button" class="cms-live-icon cms-live-close" x-on:click="overlayOpen = false" title="Cerrar vista previa" aria-label="Cerrar vista previa"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M6 6l12 12M18 6 6 18"/></svg></button>
            </div>
        </div>

        <div class="cms-live-stage" x-ref="stage">
            <iframe x-ref="frame" class="cms-live-frame" title="Vista previa"></iframe>
            <div class="cms-live-notice" x-show="notice" x-cloak x-bind:data-blocking="blocking" role="status">
                <p x-text="notice"></p>
            </div>
        </div>

        <p class="cms-live-hint"><span aria-hidden="true">↖</span> Haz clic en un texto resaltado para ir a su campo. Los cambios se ven aquí antes de guardar.</p>
    </aside>

    <button type="button" class="cms-live-reopen" x-on:click="toggleCollapsed()" x-cloak>
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12Z"/><circle cx="12" cy="12" r="3"/></svg>
        <span>Vista previa</span>
    </button>
    <button type="button" class="cms-live-fab" x-on:click="openOverlay()">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12Z"/><circle cx="12" cy="12" r="3"/></svg>
        <span>Vista previa</span>
    </button>
</div>
