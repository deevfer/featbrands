<x-filament-panels::page>
    <div style="display:grid;gap:20px">
        @forelse ($submissions as $submission)
            <x-filament::section>
                <x-slot name="heading">{{ $submission->first_name }} {{ $submission->last_name }} · {{ $submission->company }}</x-slot>
                <x-slot name="description">{{ $submission->created_at }} · {{ strtoupper($submission->market) }} · {{ strtoupper($submission->locale) }}</x-slot>
                <dl style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:20px;font-size:14px;line-height:1.7">
                    <div><dt style="font-weight:600">Email</dt><dd><a href="mailto:{{ $submission->email }}">{{ $submission->email }}</a></dd></div>
                    <div><dt style="font-weight:600">Teléfono</dt><dd><a href="tel:{{ $submission->country_code.$submission->phone }}">{{ $submission->country_code }} {{ $submission->phone }}</a></dd></div>
                    <div><dt style="font-weight:600">Cargo</dt><dd>{{ $submission->job_title ?: 'No indicado' }}</dd></div>
                    <div><dt style="font-weight:600">Presupuesto</dt><dd>{{ $submission->budget ?: 'Por definir' }}</dd></div>
                    <div><dt style="font-weight:600">¿Cómo se enteró de Feat.?</dt><dd>{{ $referrals[$submission->referral_source] ?? $submission->referral_source }}</dd></div>
                </dl>
                <div style="border-top:1px solid #8884;padding-top:20px;margin-top:20px"><strong style="font-size:14px">Solicitud</strong><p style="white-space:pre-wrap;overflow-wrap:anywhere;line-height:1.8;font-size:14px;margin-top:10px">{{ $submission->message }}</p></div>
            </x-filament::section>
        @empty
            <x-filament::section>Aún no hay solicitudes de contacto.</x-filament::section>
        @endforelse
    </div>
    {{ $submissions->links() }}
</x-filament-panels::page>
