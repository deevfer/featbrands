<x-filament-panels::page>
    <link rel="stylesheet" href="{{ asset('css/seo-admin.css').'?v='.filemtime(public_path('css/seo-admin.css')) }}">
    <div class="seo-dashboard" wire:loading.class="seo-is-loading" wire:target="analyze,verifyPublication,mountAction,callMountedAction">
        @if ($report)
            <section class="seo-hero">
                <div class="seo-gauge" role="img" aria-label="Optimización interna: {{ $report['score'] }} de 100">
                    <svg viewBox="0 0 160 160" aria-hidden="true"><circle class="seo-gauge-track" cx="80" cy="80" r="68"/><circle class="seo-gauge-value" cx="80" cy="80" r="68" stroke-dasharray="427.26" stroke-dashoffset="{{ 427.26 * (1 - $report['score'] / 100) }}"/></svg>
                    <div><strong>{{ $report['score'] }}</strong><span>de 100</span></div>
                </div>
                <div class="seo-hero-copy"><span class="seo-eyebrow">FEAT. / VISIBILIDAD</span><h2>{{ $report['score'] >= 90 ? 'Una base sólida para ser descubierto.' : ($report['score'] >= 70 ? 'Vamos bien. Hay espacio para mejorar.' : 'Convirtamos los pendientes en mejoras.') }}</h2><p>Estado técnico y editorial de {{ $audit->page_count }} páginas. La nota es propia de este CMS; no es una calificación de Google, ChatGPT o Perplexity.</p><div class="seo-hero-meta"><span>{{ $report['environment'] === 'production' ? 'Servidor de producción' : 'Entorno de desarrollo' }}</span><span>Último análisis: {{ $audit->created_at->timezone('America/Guatemala')->format('d/m/Y H:i') }} · Guatemala</span></div></div>
            </section>
            <div class="seo-summary-grid">
                @foreach ([['Infraestructura', $report['site_score'], '40% de la nota general'], ['Páginas y servicios', $report['pages_score'], $report['articles_score'] === null ? '60% de la nota general' : '35% de la nota general'], ['Artículos del blog', $report['articles_score'], '25% de la nota general']] as [$label, $value, $weight])
                    <div class="seo-metric"><span>{{ $label }}</span><strong>{{ $value === null ? '—' : $value.'/100' }}</strong><small>{{ $value === null ? 'Sin artículos publicados' : $weight }}</small><div class="seo-meter"><span style="width:{{ $value ?? 0 }}%"></span></div></div>
                @endforeach
            </div>
            <x-filament::section>
                <x-slot name="heading">Lo siguiente que conviene resolver</x-slot>
                <div class="seo-task-list">
                    @foreach (collect($report['global'])->where('passed', false) as $check)
                        <article><span class="seo-task-dot"></span><div><h3>{{ $check['label'] }} <span class="seo-points">{{ $check['weight'] }} puntos de infraestructura</span></h3><p>{{ $check['help'] }}</p></div></article>
                    @endforeach
                    @foreach (array_slice($report['issues'], 0, 5) as $issue)
                        <article><span class="seo-task-dot"></span><div><h3>{{ $issue['label'] }} <span class="seo-points">{{ $issue['count'] }} {{ $issue['count'] === 1 ? 'página' : 'páginas' }}</span></h3><p>{{ $issue['help'] }}</p></div></article>
                    @endforeach
                    @if (collect($report['global'])->every('passed') && ! $report['issues'])<p>Todos los criterios de esta versión del análisis están aprobados. Mantén el contenido actualizado y vuelve a comprobar la publicación periódicamente.</p>@endif
                </div>
            </x-filament::section>
            <x-filament::section>
                <x-slot name="heading">Acceso y configuración</x-slot>
                <x-slot name="description">Dominio previsto: {{ $report['origin'] }}. Las verificaciones locales y las del dominio público se muestran por separado.</x-slot>
                <div class="seo-check-grid">
                    @foreach ($report['global'] as $check)<div class="seo-check"><span class="seo-status {{ $check['passed'] ? 'seo-status-pass' : 'seo-status-warn' }}">{{ $check['passed'] ? '✓ Aprobado' : '○ Pendiente' }}</span><h3>{{ $check['label'] }}</h3><p>{{ $check['help'] }}</p><small>{{ $check['earned'] }}/{{ $check['weight'] }} puntos</small></div>@endforeach
                </div>
                @if ($report['public_verification'])<div class="seo-verification"><h3>Última comprobación externa · {{ \Carbon\Carbon::parse($report['public_verification']['checked_at'])->timezone('America/Guatemala')->format('d/m/Y H:i') }}</h3>@foreach ($report['public_verification']['checks'] as $check)<p><strong>{{ $check['passed'] ? '✓' : '○' }} {{ $check['label'] }}:</strong> {{ $check['detail'] }}</p>@endforeach<p>Estas solicitudes usan los nombres de los agentes desde la IP de tu servidor. Para confirmar el acceso de los rastreadores reales hay que revisar también las IP oficiales y los registros del hosting.</p></div>@endif
            </x-filament::section>
            <x-filament::section>
                <x-slot name="heading">Optimización por página</x-slot>
                <x-slot name="description">Páginas con menor nota primero. Edita sus metadatos y pulsa Analizar ahora para medir el cambio.</x-slot>
                <div class="seo-table-tools"><label>Buscar página<input type="search" wire:model.live.debounce.350ms="search" placeholder="Título, URL o idioma" maxlength="200"></label><label class="seo-toggle"><input type="checkbox" wire:model.live="onlyIssues"> Solo con pendientes</label></div>
                <div class="seo-table-wrap"><table class="seo-table"><thead><tr><th>Página</th><th>Nota</th><th>Detalle</th></tr></thead><tbody>
                    @forelse ($pages as $item)<tr><td><span class="seo-kind">{{ strtoupper($item['locale']) }} · {{ $item['kind'] === 'post' ? 'Artículo' : 'Página' }}</span><a href="{{ url($item['path']) }}" target="_blank" rel="noopener">{{ $item['title'] }}</a><small>{{ $item['path'] }}</small></td><td><strong class="seo-row-score">{{ $item['score'] }}<span>/100</span></strong><div class="seo-meter"><span style="width:{{ $item['score'] }}%"></span></div></td><td><details><summary>{{ count(array_filter($item['checks'], fn ($check) => ! $check['passed'])) }} pendientes</summary><ul>@foreach ($item['checks'] as $check)<li><strong>{{ $check['passed'] ? '✓' : '○' }} {{ $check['label'] }}</strong>@if (! $check['passed'])<p>{{ $check['help'] }}</p>@endif</li>@endforeach</ul></details><a class="seo-edit" href="{{ url($item['edit_url']) }}">Editar SEO ↗</a></td></tr>@empty<tr><td colspan="3">No hay páginas para este filtro.</td></tr>@endforelse
                </tbody></table></div>
                <div class="seo-pagination"><x-filament::button color="gray" wire:click="previousPage" :disabled="$pages->onFirstPage()">Anterior</x-filament::button><span>{{ $pages->currentPage() }} / {{ $pages->lastPage() }} · {{ $pages->total() }} páginas</span><x-filament::button color="gray" wire:click="nextPage" :disabled="! $pages->hasMorePages()">Siguiente</x-filament::button></div>
            </x-filament::section>
            <div class="seo-bottom-grid"><x-filament::section><x-slot name="heading">Evolución de la nota</x-slot><div class="seo-history">@foreach ($history as $run)<div><time>{{ $run->created_at->timezone('America/Guatemala')->format('d/m H:i') }}</time><div class="seo-meter"><span style="width:{{ $run->score }}%"></span></div><strong>{{ $run->score }}/100</strong></div>@endforeach</div></x-filament::section>
            <x-filament::section><x-slot name="heading">Criterios y documentación</x-slot><div class="seo-method"><p>La nota combina infraestructura (40%), promedio de páginas y servicios (35%) y promedio de artículos (25%). Si no hay artículos, las páginas representan el 60%. Cada comprobación suma su peso cuando se aprueba. La escala visible es de 1 a 100.</p><p>Los rangos de títulos y descripciones son sugerencias editoriales. No medimos posiciones, citas en respuestas de IA ni Core Web Vitals reales. Publicar, permitir rastreo y obtener 100 puntos no garantiza indexación ni recomendaciones.</p><p>Los datos de mercados, casos y perfiles se completarán con información validada. No se inventan para subir la nota.</p><div class="seo-source-links">@foreach ($sources as $name => $href)<a href="{{ $href }}" target="_blank" rel="noopener noreferrer">{{ $name }} ↗</a>@endforeach</div></div></x-filament::section></div>
        @else
            <x-filament::section><x-slot name="heading">Tu punto de partida</x-slot><p>Analiza el sitio para conocer su puntuación, revisar cada página y obtener una lista de mejoras. El análisis usa el contenido actual del proyecto y guarda el resultado.</p><x-filament::button wire:click="analyze" style="margin-top:20px">Analizar ahora</x-filament::button></x-filament::section>
        @endif
        <div wire:loading wire:target="analyze,verifyPublication,callMountedAction" class="seo-loading" role="status">Comprobando el sitio. El análisis puede tardar unos segundos…</div>
    </div>
</x-filament-panels::page>
