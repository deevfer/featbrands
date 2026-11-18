<?php

namespace App\Mail;

use App\Filament\Pages\ContactSubmissions;
use App\Models\ContactSubmission;
use App\Services\SiteContent;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Tells the team about a new contact request; replying goes straight to the person.
 */
class ContactLeadMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public ContactSubmission $submission) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->copy()['subject'].' · '.$this->submission->company,
            replyTo: [new Address($this->submission->email, $this->submission->fullName())],
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.contact-lead',
            with: [
                'copy' => $this->copy(),
                'form' => app(SiteContent::class)->get('contact', 'es'),
                'inboxUrl' => ContactSubmissions::getUrl(panel: 'admin'),
            ],
        );
    }

    /**
     * @return array<string, string>
     */
    private function copy(): array
    {
        return app(SiteContent::class)->get('email_lead', 'es');
    }
}
