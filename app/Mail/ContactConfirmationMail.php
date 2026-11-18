<?php

namespace App\Mail;

use App\Models\ContactSubmission;
use App\Models\SiteSetting;
use App\Services\SiteContent;
use App\Services\SiteSeo;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Confirms to the person, in the language they used, that their request arrived.
 */
class ContactConfirmationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public ContactSubmission $submission) {}

    public function envelope(): Envelope
    {
        $settings = SiteSetting::current();
        $replyTo = $settings->mailRecipients()[0] ?? $settings->mail_from_address;

        return new Envelope(
            subject: $this->copy()['subject'],
            replyTo: filled($replyTo) ? [new Address($replyTo, $settings->mail_from_name ?: 'Feat.')] : [],
        );
    }

    public function content(): Content
    {
        $copy = $this->copy();
        $href = $copy['ctaHref'];

        return new Content(
            view: 'emails.contact-confirmation',
            with: [
                'copy' => $copy,
                'form' => app(SiteContent::class)->get('contact', $this->language()),
                'ctaUrl' => preg_match('~^https?://~i', $href) ? $href : app(SiteSeo::class)->absolute($href),
            ],
        );
    }

    private function language(): string
    {
        return $this->submission->locale === 'en' ? 'en' : 'es';
    }

    /**
     * @return array<string, string>
     */
    private function copy(): array
    {
        return app(SiteContent::class)->get('email_confirmation', $this->language());
    }
}
