<?php

namespace App\Services;

use App\Models\SiteSetting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Checks a reCAPTCHA v3 token against Google with the keys and minimum score saved in the CMS.
 */
class RecaptchaVerifier
{
    public function verify(SiteSetting $settings, string $token, string $action, ?string $ipAddress = null): bool
    {
        if (! $settings->recaptchaIsConfigured()) {
            return false;
        }

        try {
            $response = Http::asForm()
                ->acceptJson()
                ->timeout(8)
                ->retry(2, 200, throw: false)
                ->post('https://www.google.com/recaptcha/api/siteverify', [
                    'secret' => $settings->recaptcha_secret_key,
                    'response' => $token,
                    'remoteip' => $ipAddress,
                ]);

            $result = $response->successful() ? $response->json() : [];

            return ($result['success'] ?? false) === true
                && ($result['action'] ?? null) === $action
                && (float) ($result['score'] ?? 0) >= $settings->recaptchaMinScore();
        } catch (Throwable $exception) {
            Log::warning('reCAPTCHA verification failed.', ['message' => $exception->getMessage()]);

            return false;
        }
    }
}
