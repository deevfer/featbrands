<?php

namespace App\Mail;

use App\Models\SiteSetting;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Sent from the CMS to check the SMTP account before relying on it for leads.
 */
class SettingsTestMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public SiteSetting $settings) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Correo de prueba — Feat.');
    }

    public function content(): Content
    {
        return new Content(view: 'emails.settings-test');
    }
}
