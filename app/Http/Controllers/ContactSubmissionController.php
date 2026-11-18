<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreContactSubmissionRequest;
use App\Models\ContactSubmission;
use App\Services\LeadNotifications;
use App\Services\SiteContent;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

class ContactSubmissionController extends Controller
{
    public function store(StoreContactSubmissionRequest $request, LeadNotifications $notifications): JsonResponse|RedirectResponse
    {
        $data = $request->safe()->except(['website', 'phone_country', 'g-recaptcha-response']);
        $data['phone'] = preg_replace('/\D/', '', $data['phone']);
        $submission = ContactSubmission::create($data);

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
}
