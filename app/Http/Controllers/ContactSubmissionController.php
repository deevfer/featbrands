<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreContactSubmissionRequest;
use App\Models\ContactIntent;
use App\Models\ContactSubmission;
use App\Services\LeadNotifications;
use App\Services\SiteContent;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

class ContactSubmissionController extends Controller
{
    public function store(StoreContactSubmissionRequest $request, LeadNotifications $notifications): JsonResponse|RedirectResponse
    {
        $data = $request->safe()->except(['website', 'phone_country', 'g-recaptcha-response', 'intent_key']);
        $data['phone'] = preg_replace('/\D/', '', $data['phone']);
        $submission = ContactSubmission::create($data);

        $this->markIntentsSubmitted($submission, $request->validated('intent_key'));

        defer(fn () => $notifications->contactSubmitted($submission));

        if ($request->expectsJson()) {
            return response()->json([
                'message' => app(SiteContent::class)->get('contact', $data['locale'])['success'],
                'id' => $submission->id,
            ], 201);
        }

        return to_route('contact', ['lang' => $data['locale'], 'market' => $data['market']])
            ->with('contact_submitted', true);
    }

    /**
     * The form in progress, and any other unsent form with the same email, no longer needs a follow-up.
     */
    private function markIntentsSubmitted(ContactSubmission $submission, ?string $intentKey): void
    {
        ContactIntent::query()
            ->whereNull('submitted_at')
            ->where(fn ($query) => $query->where('email', mb_strtolower(trim($submission->email)))
                ->when($intentKey, fn ($query) => $query->orWhere('intent_key', $intentKey)))
            ->update(['submitted_at' => now(), 'contact_submission_id' => $submission->id]);
    }
}
