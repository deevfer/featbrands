<?php

namespace App\Console\Commands;

use App\Models\ContactIntent;
use App\Models\ContactSubmission;
use App\Services\LeadNotifications;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('contact:notify-abandoned')]
#[Description('Avisa al equipo de los formularios de contacto que se empezaron y no se enviaron en 30 minutos')]
class NotifyAbandonedContacts extends Command
{
    /** Older unsent forms are not reported, so turning email on later does not send a backlog. */
    private const MAX_AGE_DAYS = 7;

    public function handle(LeadNotifications $notifications): int
    {
        $notified = 0;
        $alreadySent = 0;

        ContactIntent::query()
            ->abandoned()
            ->where('last_activity_at', '>=', now()->subDays(self::MAX_AGE_DAYS))
            ->orderBy('id')
            ->each(function (ContactIntent $intent) use ($notifications, &$notified, &$alreadySent): void {
                $submission = ContactSubmission::query()
                    ->whereRaw('lower(email) = ?', [$intent->email])
                    ->where('created_at', '>=', $intent->created_at)
                    ->latest('id')
                    ->first();

                if ($submission !== null) {
                    $intent->update(['submitted_at' => $submission->created_at, 'contact_submission_id' => $submission->id]);
                    $alreadySent++;

                    return;
                }

                if ($notifications->contactAbandoned($intent)) {
                    $intent->update(['abandoned_notified_at' => now()]);
                    $notified++;
                }
            });

        $this->info("Formularios sin completar avisados: {$notified}. Ya enviados por otra vía: {$alreadySent}.");

        return self::SUCCESS;
    }
}
