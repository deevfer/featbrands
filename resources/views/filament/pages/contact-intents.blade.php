<x-filament-panels::page>
    <x-filament::section>
        Personas que empezaron el formulario de contacto y no lo enviaron. Se guardan desde que escriben su nombre y un correo válido; si lo envían después, salen de esta lista. A los {{ $waitMinutes }} minutos sin actividad, el equipo recibe un aviso por correo. Los identificadores publicitarios solo se guardan si la persona aceptó las cookies de marketing.
    </x-filament::section>
    <div style="display:grid;gap:20px">
        @forelse ($intents as $intent)
            <x-filament::section>
                <x-slot name="heading">{{ $intent->fullName() }}@if ($intent->company) · {{ $intent->company }}@endif</x-slot>
                <x-slot name="description">Última actividad: {{ $intent->last_activity_at->timezone('America/Guatemala')->format('d/m/Y H:i') }} · {{ strtoupper($intent->market) }} · {{ strtoupper($intent->locale) }} · {{ $intent->abandoned_notified_at ? 'Aviso enviado' : 'Aviso pendiente' }}</x-slot>
                <dl style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:20px;font-size:14px;line-height:1.7">
                    <div><dt style="font-weight:600">Email</dt><dd><a href="mailto:{{ $intent->email }}">{{ $intent->email }}</a></dd></div>
                    <div><dt style="font-weight:600">Teléfono</dt><dd>@if ($intent->phone)<a href="tel:{{ $intent->country_code.$intent->phone }}">{{ $intent->fullPhone() }}</a>@else No indicado @endif</dd></div>
                    <div><dt style="font-weight:600">Cargo</dt><dd>{{ $intent->job_title ?: 'No indicado' }}</dd></div>
                    <div><dt style="font-weight:600">Presupuesto</dt><dd>{{ $intent->budget ?: 'No indicado' }}</dd></div>
                    <div><dt style="font-weight:600">¿Cómo se enteró de Feat.?</dt><dd>{{ $intent->referral_source ? ($referrals[$intent->referral_source] ?? $intent->referral_source) : 'No indicado' }}</dd></div>
                    <div><dt style="font-weight:600">Cookies de retargeting</dt><dd style="overflow-wrap:anywhere">@if ($intent->marketing_consent && $intent->tracking)@foreach ($intent->tracking as $key => $value)<div>{{ $key }}: {{ $value }}</div>@endforeach @else No aceptó cookies de marketing @endif</dd></div>
                </dl>
                @if ($intent->message)
                    <div style="border-top:1px solid #8884;padding-top:20px;margin-top:20px"><strong style="font-size:14px">Mensaje (incompleto)</strong><p style="white-space:pre-wrap;overflow-wrap:anywhere;line-height:1.8;font-size:14px;margin-top:10px">{{ $intent->message }}</p></div>
                @endif
            </x-filament::section>
        @empty
            <x-filament::section>No hay formularios sin completar.</x-filament::section>
        @endforelse
    </div>
    {{ $intents->links() }}
</x-filament-panels::page>
