<?php

namespace App\Mail;

use App\Filament\Pages\ContactIntents;
use App\Models\ContactIntent;
use App\Services\SiteContent;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Tells the team that someone started the contact form and did not send it within 30 minutes.
 */
class ContactAbandonedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public ContactIntent $intent) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->copy()['subject'].' · '.($this->intent->company ?: $this->intent->fullName()),
            replyTo: [new Address($this->intent->email, $this->intent->fullName())],
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.contact-abandoned',
            with: [
                'copy' => $this->copy(),
                'form' => app(SiteContent::class)->get('contact', 'es'),
                'inboxUrl' => ContactIntents::getUrl(panel: 'admin'),
            ],
        );
    }

    /**
     * @return array<string, string>
     */
    private function copy(): array
    {
        return app(SiteContent::class)->get('email_abandoned', 'es');
    }
}
