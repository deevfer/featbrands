<?php

namespace App\Services;

use App\Mail\ContactConfirmationMail;
use App\Mail\ContactLeadMail;
use App\Models\ContactSubmission;
use App\Models\SiteSetting;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

/**
 * Emails sent for each new lead: one to every team address and a confirmation to the person.
 * A failing mail server never loses the request; it is already saved and the error is logged.
 */
class LeadNotifications
{
    public function contactSubmitted(ContactSubmission $submission): void
    {
        $settings = SiteSetting::current();

        if (! $settings->canSendMail()) {
            return;
        }

        try {
            $settings->applyMailConfiguration();
        } catch (Throwable $exception) {
            $this->logFailure($submission, $exception);

            return;
        }

        foreach ($settings->mailRecipients() as $recipient) {
            $this->send(new ContactLeadMail($submission), $recipient, $submission);
        }

        $this->send(new ContactConfirmationMail($submission), $submission->email, $submission);
    }

    private function send(Mailable $mail, string $recipient, ContactSubmission $submission): void
    {
        try {
            Mail::to($recipient)->send($mail);
        } catch (Throwable $exception) {
            $this->logFailure($submission, $exception, $recipient);
        }
    }

    private function logFailure(ContactSubmission $submission, Throwable $exception, ?string $recipient = null): void
    {
        Log::warning('Lead notification could not be sent.', [
            'contact_submission_id' => $submission->getKey(),
            'recipient' => $recipient,
            'message' => $exception->getMessage(),
        ]);
    }
}
