<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

/**
 * Site-wide integrations edited from the CMS: outgoing mail, reCAPTCHA and measurement tags.
 */
#[Fillable([
    'mail_enabled', 'mail_mailer', 'mail_host', 'mail_port', 'mail_encryption', 'mail_username', 'mail_password',
    'mail_from_address', 'mail_from_name', 'mail_to_address',
    'recaptcha_enabled', 'recaptcha_site_key', 'recaptcha_secret_key', 'recaptcha_min_score',
    'google_analytics_id', 'google_tag_manager_id', 'meta_pixel_id', 'google_ads_id',
    'linkedin_enabled', 'linkedin_partner_id', 'linkedin_contact_conversion_id', 'linkedin_form_start_conversion_id',
    'linkedin_form_submit_conversion_id', 'linkedin_lead_conversion_id',
    'custom_head_scripts', 'custom_body_scripts',
])]
class SiteSetting extends Model
{
    protected function casts(): array
    {
        return [
            'mail_enabled' => 'boolean',
            'mail_port' => 'integer',
            'mail_password' => 'encrypted',
            'recaptcha_enabled' => 'boolean',
            'recaptcha_secret_key' => 'encrypted',
            'recaptcha_min_score' => 'float',
            'linkedin_enabled' => 'boolean',
        ];
    }

    public static function current(): self
    {
        return static::query()->firstOrCreate(['id' => 1], [
            'mail_enabled' => false,
            'mail_mailer' => 'smtp',
            'mail_port' => 587,
            'mail_encryption' => 'tls',
            'mail_from_name' => 'Feat.',
            'recaptcha_enabled' => false,
            'recaptcha_min_score' => 0.5,
        ]);
    }

    public function canSendMail(): bool
    {
        if (! $this->mail_enabled) {
            return false;
        }

        return ($this->mail_mailer ?: 'smtp') !== 'smtp' || (filled($this->mail_host) && filled($this->mail_port));
    }

    /**
     * Team addresses that receive every new lead.
     *
     * @return array<int, string>
     */
    public function mailRecipients(): array
    {
        return collect(explode(',', (string) $this->mail_to_address))
            ->map(fn (string $email): string => mb_strtolower(trim($email)))
            ->filter(fn (string $email): bool => filter_var($email, FILTER_VALIDATE_EMAIL) !== false)
            ->unique()
            ->values()
            ->all();
    }

    /**
     * Points Laravel's mailer at the SMTP account saved in the CMS for the rest of the request.
     */
    public function applyMailConfiguration(): void
    {
        $mailer = $this->mail_mailer ?: 'smtp';

        config([
            'mail.default' => $mailer,
            'mail.from.address' => $this->mail_from_address ?: config('mail.from.address'),
            'mail.from.name' => $this->mail_from_name ?: 'Feat.',
        ]);

        if ($mailer === 'smtp') {
            config([
                'mail.mailers.smtp.host' => $this->mail_host,
                'mail.mailers.smtp.port' => $this->mail_port,
                'mail.mailers.smtp.username' => $this->mail_username,
                'mail.mailers.smtp.password' => $this->mail_password,
                'mail.mailers.smtp.scheme' => $this->mail_encryption === 'ssl' ? 'smtps' : 'smtp',
                'mail.mailers.smtp.auto_tls' => $this->mail_encryption !== 'none',
                'mail.mailers.smtp.timeout' => 10,
            ]);
        }

        app('mail.manager')->purge($mailer);
    }

    public function recaptchaIsConfigured(): bool
    {
        return $this->recaptcha_enabled && filled($this->recaptcha_site_key) && filled($this->recaptcha_secret_key);
    }

    public function recaptchaMinScore(): float
    {
        return max(0.1, min(1.0, (float) ($this->recaptcha_min_score ?: 0.5)));
    }

    /**
     * @return array{enabled: bool, partnerId: string|null, conversions: array<string, string|null>}
     */
    public function linkedInConfiguration(): array
    {
        $validId = fn (?string $id): ?string => preg_match('/^[0-9]{1,20}$/', $id ?? '') ? $id : null;

        return [
            'enabled' => $this->linkedin_enabled && $validId($this->linkedin_partner_id) !== null,
            'partnerId' => $validId($this->linkedin_partner_id),
            'conversions' => [
                'contact' => $validId($this->linkedin_contact_conversion_id),
                'form_start' => $validId($this->linkedin_form_start_conversion_id),
                'form_submit' => $validId($this->linkedin_form_submit_conversion_id),
                'lead' => $validId($this->linkedin_lead_conversion_id),
            ],
        ];
    }
}
