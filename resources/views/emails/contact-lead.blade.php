<x-emails.layout :title="$copy['title']" :eyebrow="$copy['eyebrow']" :preheader="$submission->fullName().' · '.$submission->company" :footer="$copy['footer']">
    <p style="margin:0;"><strong>{{ $submission->fullName() }}</strong> {{ $copy['intro'] }}</p>
    @include('emails.partials.contact-details', ['form' => $form, 'submission' => $submission, 'showMarket' => true])
    <p style="margin:0;color:#6D665A;font-size:12px;line-height:20px;">Idioma: {{ $submission->locale === 'en' ? 'Inglés' : 'Español' }} · Referencia: solicitud #{{ $submission->id }}</p>
    <x-emails.button :href="$inboxUrl">{{ $copy['cta'] }}</x-emails.button>
    <p style="margin:22px 0 0;color:#6D665A;font-size:13px;line-height:21px;">{{ $copy['replyNote'] }}</p>
</x-emails.layout>
