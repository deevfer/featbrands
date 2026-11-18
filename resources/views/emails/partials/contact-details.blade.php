@php
    $fields = $form['fields'];
    $markets = ['gt' => 'Guatemala', 'cr' => 'Costa Rica', 'sv' => 'El Salvador', 'us' => 'USA'];
@endphp
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="margin:24px 0;background-color:#F7F3EA;border:1px solid #E4DECE;border-radius:16px;">
    <x-emails.detail :label="$fields['first_name']">{{ $submission->fullName() }}</x-emails.detail>
    <x-emails.detail :label="$fields['company']">{{ $submission->company }}@if ($submission->job_title) · {{ $submission->job_title }}@endif</x-emails.detail>
    <x-emails.detail :label="$fields['email']"><a href="mailto:{{ $submission->email }}" style="color:#000000;text-decoration:underline;">{{ $submission->email }}</a></x-emails.detail>
    <x-emails.detail :label="$fields['phone']">{{ $submission->fullPhone() }}</x-emails.detail>
    @if ($submission->budget)
        <x-emails.detail :label="$fields['budget']">{{ $submission->budget }}</x-emails.detail>
    @endif
    <x-emails.detail :label="$fields['referral_source']">{{ $form['referrals'][$submission->referral_source] ?? $submission->referral_source }}</x-emails.detail>
    @if ($showMarket ?? false)
        <x-emails.detail label="Mercado">{{ $markets[$submission->market] ?? strtoupper($submission->market) }}</x-emails.detail>
    @endif
    <x-emails.detail :label="$fields['message']"><div style="white-space:pre-line;">{{ $submission->message }}</div></x-emails.detail>
</table>
