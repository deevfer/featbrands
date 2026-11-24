<?php

namespace App\Http\Controllers;

use App\Mail\ContactAbandonedMail;
use App\Mail\ContactConfirmationMail;
use App\Mail\ContactLeadMail;
use App\Models\ContactIntent;
use App\Models\ContactSubmission;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * Renders the contact emails with sample data so the CMS can preview their copy.
 */
class EmailPreviewController extends Controller
{
    public function __invoke(Request $request, string $mail): Response
    {
        $english = $request->query('lang') === 'en';
        $submission = (new ContactSubmission)->forceFill([
            'id' => 128,
            'first_name' => 'Ana',
            'last_name' => 'López',
            'email' => 'ana.lopez@empresa.com',
            'country_code' => '+502',
            'phone' => '55551234',
            'company' => 'Empresa de ejemplo',
            'job_title' => $english ? 'Marketing Director' : 'Directora de Marketing',
            'budget' => 'USD 10,000',
            'referral_source' => 'linkedin',
            'message' => $english
                ? "We are launching a new product line next quarter and need a regional campaign.\nCan we schedule a call?"
                : "Vamos a lanzar una nueva línea de productos el próximo trimestre y necesitamos una campaña regional.\n¿Podemos agendar una llamada?",
            'locale' => $english ? 'en' : 'es',
            'market' => 'gt',
        ]);

        $mailable = match ($mail) {
            'confirmacion' => new ContactConfirmationMail($submission),
            'lead' => new ContactLeadMail($submission),
            'sin-completar' => new ContactAbandonedMail((new ContactIntent)->forceFill([
                ...$submission->only(['first_name', 'last_name', 'email', 'country_code', 'phone', 'company', 'job_title', 'locale', 'market']),
                'id' => 64,
                'message' => $english ? 'We are launching a new product line next' : 'Vamos a lanzar una nueva línea de productos el próximo',
                'marketing_consent' => true,
                'tracking' => ['fbp' => 'fb.1.1764000000000.1234567890', 'ga' => 'GA1.1.1234567890.1764000000'],
                'last_activity_at' => now()->subMinutes(32),
            ])),
            default => abort(404),
        };

        return response($mailable->render());
    }
}
