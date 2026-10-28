<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\File;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreContactSubmissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if (! is_string($this->input('phone_country'))) {
            return;
        }

        $country = collect(File::json(resource_path('data/calling-codes.json'))['countries'])
            ->firstWhere('iso', $this->input('phone_country'));

        if ($country !== null) {
            $this->merge(['country_code' => $country['dial_code']]);
        }
    }

    /**
     * @return array<string, array<mixed>>
     */
    public function rules(): array
    {
        return [
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'string', 'email', 'max:254'],
            'country_code' => ['required', 'string', 'regex:/^\+[1-9][0-9]{0,2}$/'],
            'phone_country' => ['nullable', Rule::in(array_column(File::json(resource_path('data/calling-codes.json'))['countries'], 'iso'))],
            'phone' => ['required', 'string', 'max:25', 'regex:/^[0-9][0-9 ()-]{3,24}$/'],
            'company' => ['required', 'string', 'max:150'],
            'job_title' => ['nullable', 'string', 'max:120'],
            'budget' => ['nullable', 'string', 'max:120'],
            'referral_source' => ['required', Rule::in(array_keys($this->copy()['referrals']))],
            'message' => ['required', 'string', 'min:20', 'max:5000'],
            'locale' => ['required', Rule::in(['es', 'en'])],
            'market' => ['required', Rule::in(['gt', 'cr', 'us'])],
            'website' => ['nullable', 'string', 'max:0'],
        ];
    }

    /**
     * @return array<callable>
     */
    public function after(): array
    {
        return [function (Validator $validator): void {
            if ($validator->errors()->hasAny(['country_code', 'phone'])) {
                return;
            }

            $digits = preg_replace('/\D/', '', $this->string('country_code').$this->string('phone'));

            if (strlen($digits) < 7 || strlen($digits) > 15) {
                $validator->errors()->add('phone', $this->copy()['validation']['phone']);
            }
        }];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [...$this->copy()['validation'], 'website.max' => $this->copy()['validation']['honeypot']];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return $this->copy()['fields'];
    }

    /**
     * @return array<string, mixed>
     */
    private function copy(): array
    {
        return File::json(resource_path('data/contact.json'))[$this->input('locale') === 'en' ? 'en' : 'es'];
    }
}
