<?php

namespace App\Http\Controllers;

use App\Mail\ContactConfirmationMail;
use App\Mail\ContactLeadMail;
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
            default => abort(404),
        };

        return response($mailable->render());
    }
}
