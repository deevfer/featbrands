<?php

namespace App\Http\Requests;

use App\Models\ContactIntent;
use App\Models\SiteSetting;
use App\Services\RecaptchaVerifier;
use App\Services\SiteContent;
use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * A contact form in progress: only the name and a valid email are required, the rest is saved as typed.
 */
class StoreContactIntentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<mixed>>
     */
    public function rules(): array
    {
        $copy = app(SiteContent::class)->get('contact', 'es');

        $rules = [
            'intent_key' => ['required', 'uuid'],
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['nullable', 'string', 'max:100'],
            'email' => ['required', 'string', 'email', 'max:254'],
            'country_code' => ['nullable', 'string', 'regex:/^\+[1-9][0-9]{0,2}$/'],
            'phone' => ['nullable', 'string', 'max:25'],
            'company' => ['nullable', 'string', 'max:150'],
            'job_title' => ['nullable', 'string', 'max:120'],
            'budget' => ['nullable', 'string', 'max:120'],
            'referral_source' => ['nullable', Rule::in(array_keys($copy['referrals']))],
            'message' => ['nullable', 'string', 'max:5000'],
            'locale' => ['required', Rule::in(['es', 'en'])],
            'market' => ['required', Rule::in(['gt', 'cr', 'sv', 'us'])],
            'marketing_consent' => ['required', 'boolean'],
            'tracking' => ['nullable', 'array:'.implode(',', array_keys(ContactIntent::TRACKING_COOKIES))],
            'tracking.*' => ['nullable', 'string', 'max:255', 'regex:/^[A-Za-z0-9._\-]+$/'],
            'website' => ['nullable', 'string', 'max:0'],
        ];

        $settings = SiteSetting::current();

        if ($settings->recaptchaIsConfigured()) {
            $rules['g-recaptcha-response'] = ['bail', 'required', 'string', function (string $attribute, mixed $value, Closure $fail) use ($settings): void {
                if (! app(RecaptchaVerifier::class)->verify($settings, (string) $value, 'contact_intent', $this->ip())) {
                    $fail('No pudimos verificar la solicitud.');
                }
            }];
        }

        return $rules;
    }
}
