<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreContactSubmissionRequest;
use App\Services\SiteContent;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class ContactSubmissionController extends Controller
{
    public function store(StoreContactSubmissionRequest $request): JsonResponse|RedirectResponse
    {
        $data = $request->safe()->except(['website', 'phone_country']);
        $data['phone'] = preg_replace('/\D/', '', $data['phone']);
        $data['created_at'] = now();
        $data['updated_at'] = now();
        DB::table('contact_submissions')->insert($data);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => app(SiteContent::class)->get('contact', $data['locale'])['success'],
            ], 201);
        }

        return to_route('contact', ['lang' => $data['locale'], 'market' => $data['market']])
            ->with('contact_submitted', true);
    }
}
