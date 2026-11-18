<?php

namespace Tests\Feature;

use App\Mail\ContactConfirmationMail;
use App\Mail\ContactLeadMail;
use App\Models\ContactSubmission;
use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ContactNotificationsTest extends TestCase
{
    use LazilyRefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutDefer();
    }

    public function test_a_new_request_notifies_the_team_and_confirms_to_the_person(): void
    {
        Mail::fake();
        $this->enableMail(['mail_to_address' => 'ventas@featbrands.com, Equipo@featbrands.com']);

        $response = $this->postJson('/contacto', $this->payload());

        $response->assertCreated()->assertJsonPath('id', ContactSubmission::sole()->id);
        Mail::assertSent(ContactLeadMail::class, 2);
        Mail::assertSent(ContactLeadMail::class, fn (ContactLeadMail $mail): bool => $mail->hasTo('equipo@featbrands.com') && $mail->hasReplyTo('ana@example.com'));
        Mail::assertSent(ContactConfirmationMail::class, fn (ContactConfirmationMail $mail): bool => $mail->hasTo('ana@example.com') && $mail->hasReplyTo('ventas@featbrands.com'));
    }

    public function test_requests_are_saved_without_emails_while_sending_is_off(): void
    {
        Mail::fake();

        $this->postJson('/contacto', $this->payload())->assertCreated();

        $this->assertDatabaseCount('contact_submissions', 1);
        Mail::assertNothingSent();
    }

    public function test_a_failing_mail_server_never_loses_the_request(): void
    {
        Log::spy();
        $this->enableMail(['mail_host' => '127.0.0.1', 'mail_port' => 1, 'mail_encryption' => 'none']);

        $this->postJson('/contacto', $this->payload())->assertCreated();

        $this->assertDatabaseCount('contact_submissions', 1);
        Log::shouldHaveReceived('warning')->withArgs(fn (string $message): bool => $message === 'Lead notification could not be sent.')->atLeast()->once();
    }

    public function test_confirmation_uses_the_language_and_brand_of_the_site(): void
    {
        $submission = ContactSubmission::create([...$this->payload(), 'locale' => 'en', 'phone' => '55551234']);

        $html = (new ContactConfirmationMail($submission))->render();

        $this->assertStringContainsString('Thank you for reaching out.', $html);
        $this->assertStringContainsString('assets/email/feat-white.png', $html);
        $this->assertStringContainsString('#FDDB02', $html);
        $this->assertSame('We received your message — Feat.', (new ContactConfirmationMail($submission))->envelope()->subject);
    }

    public function test_the_team_email_links_to_the_requests_in_the_cms(): void
    {
        $submission = ContactSubmission::create([...$this->payload(), 'phone' => '55551234']);

        $html = (new ContactLeadMail($submission))->render();

        $this->assertStringContainsString('/admin/contact-submissions', $html);
        $this->assertStringContainsString('Ana Pérez', $html);
        $this->assertStringContainsString('+502 55551234', $html);
        $this->assertStringContainsString('Guatemala', $html);
    }

    public function test_recaptcha_blocks_bots_when_it_is_enabled(): void
    {
        SiteSetting::current()->update(['recaptcha_enabled' => true, 'recaptcha_site_key' => 'site-key', 'recaptcha_secret_key' => 'secret', 'recaptcha_min_score' => 0.5]);
        Http::fake(['www.google.com/recaptcha/*' => Http::sequence()
            ->push(['success' => true, 'action' => 'contact', 'score' => 0.2])
            ->push(['success' => true, 'action' => 'contact', 'score' => 0.9])]);

        $this->postJson('/contacto', $this->payload())->assertUnprocessable()->assertJsonValidationErrors('g-recaptcha-response');
        $this->postJson('/contacto', [...$this->payload(), 'g-recaptcha-response' => 'bot'])->assertUnprocessable()->assertJsonValidationErrors(['g-recaptcha-response' => 'No pudimos verificar']);
        $this->postJson('/contacto', [...$this->payload(), 'g-recaptcha-response' => 'person'])->assertCreated();

        $this->assertDatabaseCount('contact_submissions', 1);
        $this->get('/contacto')->assertSee('data-recaptcha-site-key="site-key"', false)->assertSee('recaptcha/api.js?render=site-key', false);
    }

    public function test_email_previews_are_only_for_the_cms(): void
    {
        $this->get('/admin/correos/vista-previa/confirmacion')->assertRedirect('/admin/login');

        $this->actingAs(User::factory()->create())
            ->get('/admin/correos/vista-previa/confirmacion?lang=en')
            ->assertOk()
            ->assertSee('Thank you for reaching out.');
        $this->get('/admin/correos/vista-previa/otro')->assertNotFound();
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    private function enableMail(array $overrides = []): void
    {
        SiteSetting::current()->update([
            'mail_enabled' => true, 'mail_mailer' => 'smtp', 'mail_host' => 'smtp.example.com', 'mail_port' => 587,
            'mail_from_address' => 'web@featbrands.com', 'mail_to_address' => 'ventas@featbrands.com', ...$overrides,
        ]);
    }

    /**
     * @return array<string, string>
     */
    private function payload(): array
    {
        return [
            'first_name' => 'Ana', 'last_name' => 'Pérez', 'email' => 'ana@example.com',
            'country_code' => '+502', 'phone' => '5555-1234', 'company' => 'Marca de prueba',
            'job_title' => 'Marketing', 'budget' => 'USD 5,000', 'referral_source' => 'linkedin',
            'message' => 'Necesitamos una estrategia para el lanzamiento de nuestra marca.',
            'locale' => 'es', 'market' => 'gt', 'website' => '',
        ];
    }
}
