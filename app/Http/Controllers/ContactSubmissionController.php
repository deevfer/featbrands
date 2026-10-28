<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreContactSubmissionRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

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
                'message' => File::json(resource_path('data/contact.json'))[$data['locale']]['success'],
            ], 201);
        }

        return to_route('contact', ['lang' => $data['locale'], 'market' => $data['market']])
            ->with('contact_submitted', true);
    }
}
