<x-emails.layout :title="$copy['title']" :accent="$copy['accent']" :eyebrow="$copy['eyebrow']" :preheader="$copy['preheader']" :footer="$copy['footer']" :lang="$submission->locale">
    <p style="margin:0 0 16px;">{{ $copy['greeting'] }} <strong>{{ $submission->first_name }}</strong>,</p>
    <p style="margin:0;">{{ $copy['body'] }}</p>
    <p style="margin:32px 0 0;color:#6D665A;font-size:11px;font-weight:600;line-height:18px;letter-spacing:3px;text-transform:uppercase;">{{ $copy['summaryTitle'] }}</p>
    @include('emails.partials.contact-details', ['form' => $form, 'submission' => $submission])
    <p style="margin:0;padding:18px 20px;background-color:#FFF8CC;border-left:3px solid #FDDB02;border-radius:10px;color:#1F1F1F;font-size:14px;line-height:23px;">{{ $copy['replyNote'] }}</p>
    <x-emails.button :href="$ctaUrl">{{ $copy['cta'] }}</x-emails.button>
    <p style="margin:32px 0 0;font-weight:600;">{{ $copy['signature'] }}</p>
</x-emails.layout>
