<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreContactIntentRequest;
use App\Models\ContactIntent;
use Illuminate\Http\JsonResponse;

/**
 * Saves the contact form while the visitor types, so an unsent form can be followed up after 30 minutes.
 */
class ContactIntentController extends Controller
{
    public function store(StoreContactIntentRequest $request): JsonResponse
    {
        $data = $request->safe()->except(['intent_key', 'website', 'g-recaptcha-response']);
        $data['email'] = mb_strtolower(trim($data['email']));
        $data['phone'] = filled($data['phone'] ?? null) ? preg_replace('/\D/', '', $data['phone']) : null;
        $data['tracking'] = $data['marketing_consent'] ? (array_filter($data['tracking'] ?? []) ?: null) : null;

        $intent = ContactIntent::query()->firstOrNew(['intent_key' => $request->validated('intent_key')]);

        if ($intent->submitted_at === null) {
            $intent->fill([...$data, 'last_activity_at' => now()])->save();
        }

        return response()->json(['saved' => true], $intent->wasRecentlyCreated ? 201 : 200)
            ->header('Cache-Control', 'no-store');
    }
}
