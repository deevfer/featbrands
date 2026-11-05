<x-filament-widgets::widget>
    <x-filament::section>
        <div style="display:flex;align-items:center;justify-content:space-between;gap:20px;flex-wrap:wrap">
            <div><h2 style="font-weight:600;font-size:18px">SEO y búsquedas con IA</h2><p style="font-size:13px;opacity:.65;margin-top:5px">{{ $audit ? $audit->page_count.' páginas analizadas · '.$audit->created_at->timezone('America/Guatemala')->format('d/m/Y H:i') : 'Analiza el sitio para conocer su estado.' }}</p></div>
            <div style="display:flex;align-items:center;gap:22px"><strong style="font-size:36px;letter-spacing:-1px">{{ $audit?->score ?? '—' }}<span style="font-size:14px;opacity:.6">/100</span></strong><x-filament::button tag="a" :href="\App\Filament\Pages\SeoDashboard::getUrl()">Ver dashboard SEO</x-filament::button></div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
