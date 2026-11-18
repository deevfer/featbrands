<?php

namespace Tests\Feature;

use App\Filament\Pages\ManageEmailTexts;
use App\Filament\Pages\SeoSettings;
use App\Filament\Pages\SiteSettings;
use App\Mail\SettingsTestMail;
use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;
use Tests\TestCase;

class SiteSettingsTest extends TestCase
{
    use LazilyRefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->create());
    }

    public function test_smtp_settings_are_saved_with_encrypted_secrets_that_survive_a_blank_field(): void
    {
        Livewire::test(SiteSettings::class)
            ->fillForm([
                'mail_enabled' => true, 'mail_mailer' => 'smtp', 'mail_host' => 'mail.featbrands.com', 'mail_port' => 465,
                'mail_encryption' => 'ssl', 'mail_username' => 'web@featbrands.com', 'mail_password' => 'clave-secreta',
                'mail_from_address' => 'web@featbrands.com', 'mail_to_address' => ['ventas@featbrands.com', 'hola@featbrands.com'],
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $settings = SiteSetting::current();
        $this->assertSame('clave-secreta', $settings->mail_password);
        $this->assertNotSame('clave-secreta', DB::table('site_settings')->value('mail_password'));
        $this->assertSame(['ventas@featbrands.com', 'hola@featbrands.com'], $settings->mailRecipients());

        Livewire::test(SiteSettings::class)->assertFormSet(['mail_password' => null])->fillForm(['mail_host' => 'smtp.featbrands.com'])->call('save');

        $this->assertSame('clave-secreta', SiteSetting::current()->mail_password);
        $this->assertSame('smtp.featbrands.com', SiteSetting::current()->mail_host);
    }

    public function test_required_fields_follow_what_is_enabled(): void
    {
        Livewire::test(SiteSettings::class)
            ->fillForm(['mail_enabled' => true, 'mail_mailer' => 'smtp', 'mail_host' => null, 'recaptcha_enabled' => true, 'google_analytics_id' => 'UA-123'])
            ->call('save')
            ->assertHasFormErrors(['mail_host' => 'required', 'mail_from_address' => 'required', 'recaptcha_site_key' => 'required', 'recaptcha_secret_key' => 'required', 'google_analytics_id' => 'regex']);
    }

    public function test_a_test_email_checks_the_saved_account(): void
    {
        Mail::fake();
        SiteSetting::current()->update(['mail_enabled' => true, 'mail_mailer' => 'log', 'mail_from_address' => 'web@featbrands.com', 'mail_to_address' => 'ventas@featbrands.com']);

        Livewire::test(SiteSettings::class)->callAction('sendTestMail')->assertNotified('Correo de prueba enviado a ventas@featbrands.com');

        Mail::assertSent(SettingsTestMail::class, fn (SettingsTestMail $mail): bool => $mail->hasTo('ventas@featbrands.com'));
    }

    public function test_tags_wait_for_cookie_consent(): void
    {
        $this->get('/')->assertOk()->assertSee('id="cookie-panel"', false)->assertDontSee('FEAT_META')->assertDontSee('googletagmanager.com/gtag');

        SiteSetting::current()->update([
            'google_analytics_id' => 'G-ABC123', 'meta_pixel_id' => '123456789', 'linkedin_enabled' => true, 'linkedin_partner_id' => '987',
            'custom_head_scripts' => '<script src="https://static.hotjar.com/c/hotjar-1.js"></script>',
        ]);

        $this->get('/nosotros')
            ->assertSee('<script type="text/plain" data-feat-consent="analytics" data-feat-google-loader="true" async data-src="https://www.googletagmanager.com/gtag/js?id=G-ABC123"></script>', false)
            ->assertSee('window.FEAT_META = ', false)
            ->assertSee('123456789')
            ->assertSee('window.FEAT_LINKEDIN', false)
            ->assertSee('<script type="text/plain" data-feat-consent="marketing" data-src="https://static.hotjar.com/c/hotjar-1.js"></script>', false)
            ->assertDontSee('<script src="https://static.hotjar.com', false);
    }

    public function test_extra_verification_tags_are_published_and_only_as_meta_tags(): void
    {
        Livewire::test(SeoSettings::class)
            ->fillForm([
                'facebook_domain_verification' => 'abc123fb',
                'custom_verification_tags' => '<meta name="yandex-verification" content="y123"><script>alert(1)</script>',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->get('/')
            ->assertSee('<meta name="facebook-domain-verification" content="abc123fb">', false)
            ->assertSee('<meta name="yandex-verification" content="y123">', false)
            ->assertDontSee('<script>alert(1)</script>', false);
    }

    public function test_email_copy_is_edited_with_a_live_preview_of_the_email(): void
    {
        $page = Livewire::test(ManageEmailTexts::class)->set('data.c0.es.title', 'Gracias, ya leímos tu mensaje.');

        $preview = $page->instance()->renderPreview('es', 'data.c0.es.title');

        $this->assertSame(200, $preview['status']);
        $this->assertStringContainsString('Gracias, ya leímos tu mensaje.', $preview['html']);
        $this->assertSame('/admin/correos/vista-previa/confirmacion?lang=es', $preview['path']);
        $this->assertStringContainsString('Hay una nueva conversación.', $page->instance()->renderPreview('es', 'data.c1.es.title')['html']);
    }
}
