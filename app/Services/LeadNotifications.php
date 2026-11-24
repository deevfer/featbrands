<?php

namespace App\Services;

use App\Mail\ContactAbandonedMail;
use App\Mail\ContactConfirmationMail;
use App\Mail\ContactLeadMail;
use App\Models\ContactIntent;
use App\Models\ContactSubmission;
use App\Models\SiteSetting;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

/**
 * Emails sent for each new lead: one to every team address and a confirmation to the person,
 * plus a team alert for contact forms left unsent.
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

    /**
     * Tells the team about a form that was started and not sent. Returns false when it should be retried later.
     */
    public function contactAbandoned(ContactIntent $intent): bool
    {
        $settings = SiteSetting::current();

        if (! $settings->canSendMail() || $settings->mailRecipients() === []) {
            return false;
        }

        try {
            $settings->applyMailConfiguration();
        } catch (Throwable $exception) {
            $this->logFailure($intent, $exception);

            return false;
        }

        $sent = false;

        foreach ($settings->mailRecipients() as $recipient) {
            $sent = $this->send(new ContactAbandonedMail($intent), $recipient, $intent) || $sent;
        }

        return $sent;
    }

    private function send(Mailable $mail, string $recipient, ContactSubmission|ContactIntent $lead): bool
    {
        try {
            Mail::to($recipient)->send($mail);

            return true;
        } catch (Throwable $exception) {
            $this->logFailure($lead, $exception, $recipient);

            return false;
        }
    }

    private function logFailure(ContactSubmission|ContactIntent $lead, Throwable $exception, ?string $recipient = null): void
    {
        Log::warning('Lead notification could not be sent.', [
            $lead instanceof ContactIntent ? 'contact_intent_id' : 'contact_submission_id' => $lead->getKey(),
            'recipient' => $recipient,
            'message' => $exception->getMessage(),
        ]);
    }
}
