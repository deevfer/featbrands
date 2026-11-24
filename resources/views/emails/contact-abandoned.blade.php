@php
    $fields = $form['fields'];
    $markets = ['gt' => 'Guatemala', 'cr' => 'Costa Rica', 'sv' => 'El Salvador', 'us' => 'USA'];
    $trackingLabels = ['fbp' => 'Meta (_fbp)', 'fbc' => 'Meta clic (_fbc)', 'ga' => 'Google Analytics (_ga)', 'gcl_aw' => 'Google Ads clic (_gcl_aw)', 'li_fat_id' => 'LinkedIn (li_fat_id)'];
@endphp
<x-emails.layout :title="$copy['title']" :eyebrow="$copy['eyebrow']" :preheader="$intent->fullName().' · '.$intent->email" :footer="$copy['footer']">
    <p style="margin:0;"><strong>{{ $intent->fullName() }}</strong> {{ $copy['intro'] }}</p>
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="margin:24px 0;background-color:#F7F3EA;border:1px solid #E4DECE;border-radius:16px;">
        <x-emails.detail :label="$fields['first_name']">{{ $intent->fullName() }}</x-emails.detail>
        <x-emails.detail :label="$fields['email']"><a href="mailto:{{ $intent->email }}" style="color:#000000;text-decoration:underline;">{{ $intent->email }}</a></x-emails.detail>
        @if ($intent->phone)
            <x-emails.detail :label="$fields['phone']">{{ $intent->fullPhone() }}</x-emails.detail>
        @endif
        @if ($intent->company)
            <x-emails.detail :label="$fields['company']">{{ $intent->company }}@if ($intent->job_title) · {{ $intent->job_title }}@endif</x-emails.detail>
        @endif
        @if ($intent->budget)
            <x-emails.detail :label="$fields['budget']">{{ $intent->budget }}</x-emails.detail>
        @endif
        @if ($intent->referral_source)
            <x-emails.detail :label="$fields['referral_source']">{{ $form['referrals'][$intent->referral_source] ?? $intent->referral_source }}</x-emails.detail>
        @endif
        <x-emails.detail label="Mercado">{{ $markets[$intent->market] ?? strtoupper($intent->market) }} · {{ $intent->locale === 'en' ? 'Inglés' : 'Español' }}</x-emails.detail>
        @if ($intent->message)
            <x-emails.detail :label="$fields['message']"><div style="white-space:pre-line;">{{ $intent->message }}</div></x-emails.detail>
        @endif
        <x-emails.detail :label="$copy['trackingLabel']">
            @if ($intent->marketing_consent && $intent->tracking)
                @foreach ($intent->tracking as $key => $value)
                    <div>{{ $trackingLabels[$key] ?? $key }}: {{ $value }}</div>
                @endforeach
            @else
                {{ $copy['noTracking'] }}
            @endif
        </x-emails.detail>
    </table>
    <p style="margin:0;color:#6D665A;font-size:12px;line-height:20px;">Última actividad: {{ $intent->last_activity_at?->timezone('America/Guatemala')->format('d/m/Y H:i') }} (hora de Guatemala) · Referencia: formulario #{{ $intent->id }}</p>
    <x-emails.button :href="$inboxUrl">{{ $copy['cta'] }}</x-emails.button>
    <p style="margin:22px 0 0;color:#6D665A;font-size:13px;line-height:21px;">{{ $copy['replyNote'] }}</p>
</x-emails.layout>
