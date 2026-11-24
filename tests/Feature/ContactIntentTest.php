<?php

namespace Tests\Feature;

use App\Mail\ContactAbandonedMail;
use App\Models\ContactIntent;
use App\Models\ContactSubmission;
use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Tests\TestCase;

class ContactIntentTest extends TestCase
{
    use LazilyRefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutDefer();
    }

    public function test_the_form_is_saved_once_it_has_a_name_and_a_valid_email(): void
    {
        $this->postJson('/contacto/intencion', [...$this->partial(), 'email' => 'ana@'])->assertUnprocessable()->assertJsonValidationErrors('email');
        $this->postJson('/contacto/intencion', [...$this->partial(), 'first_name' => ''])->assertUnprocessable()->assertJsonValidationErrors('first_name');

        $this->postJson('/contacto/intencion', $this->partial())->assertCreated()->assertHeader('Cache-Control', 'no-store, private');

        $intent = ContactIntent::sole();
        $this->assertSame('ana@example.com', $intent->email);
        $this->assertNull($intent->company);
        $this->assertNull($intent->tracking);
        $this->assertFalse($intent->marketing_consent);
    }

    public function test_typing_more_updates_the_same_form(): void
    {
        $this->postJson('/contacto/intencion', $this->partial())->assertCreated();
        $this->travel(5)->minutes();

        $this->postJson('/contacto/intencion', [...$this->partial(), 'company' => 'Marca de prueba', 'phone' => '5555-1234', 'message' => 'Queremos lanzar'])->assertOk();

        $intent = ContactIntent::sole();
        $this->assertSame('Marca de prueba', $intent->company);
        $this->assertSame('55551234', $intent->phone);
        $this->assertTrue($intent->last_activity_at->isAfter($intent->created_at));
    }

    public function test_ad_cookies_are_kept_only_with_marketing_consent(): void
    {
        $tracking = ['fbp' => 'fb.1.1764000000000.123', 'ga' => 'GA1.1.123.1764000000'];

        $this->postJson('/contacto/intencion', [...$this->partial(), 'tracking' => $tracking])->assertCreated();
        $this->assertNull(ContactIntent::sole()->tracking);

        $this->postJson('/contacto/intencion', [...$this->partial(), 'marketing_consent' => true, 'tracking' => $tracking])->assertOk();
        $this->assertSame($tracking, ContactIntent::sole()->tracking);

        $this->postJson('/contacto/intencion', [...$this->partial(), 'marketing_consent' => true, 'tracking' => ['other' => 'x']])->assertUnprocessable();
        $this->postJson('/contacto/intencion', [...$this->partial(), 'marketing_consent' => true, 'tracking' => ['fbp' => '<script>']])->assertUnprocessable();
    }

    public function test_sending_the_form_closes_it_and_any_other_with_the_same_email(): void
    {
        $other = ContactIntent::factory()->create(['email' => 'ana@example.com']);
        $this->postJson('/contacto/intencion', $this->partial())->assertCreated();

        $this->postJson('/contacto', [...$this->complete(), 'intent_key' => $this->key, 'email' => 'Ana@Example.com'])->assertCreated();

        $submission = ContactSubmission::sole();
        $this->assertSame(0, ContactIntent::query()->whereNull('submitted_at')->count());
        $this->assertSame($submission->id, $other->fresh()->contact_submission_id);
        $this->postJson('/contacto/intencion', [...$this->partial(), 'company' => 'Cambio'])->assertOk();
        $this->assertNull(ContactIntent::query()->where('intent_key', $this->key)->value('company'));
    }

    public function test_the_team_is_told_once_about_forms_left_for_30_minutes(): void
    {
        Mail::fake();
        $this->enableMail(['mail_to_address' => 'ventas@featbrands.com, equipo@featbrands.com']);
        $stale = ContactIntent::factory()->stale()->withTracking()->create();
        ContactIntent::factory()->create();
        ContactIntent::factory()->stale()->create(['submitted_at' => now()]);
        ContactIntent::factory()->create(['last_activity_at' => now()->subDays(10)]);

        $this->artisan('contact:notify-abandoned')->expectsOutputToContain('avisados: 1')->assertSuccessful();
        $this->artisan('contact:notify-abandoned')->expectsOutputToContain('avisados: 0')->assertSuccessful();

        Mail::assertSent(ContactAbandonedMail::class, 2);
        Mail::assertSent(ContactAbandonedMail::class, fn (ContactAbandonedMail $mail): bool => $mail->hasTo('equipo@featbrands.com') && $mail->hasReplyTo($stale->email));
        $this->assertNotNull($stale->fresh()->abandoned_notified_at);
    }

    public function test_a_form_sent_later_with_the_same_email_is_not_reported(): void
    {
        Mail::fake();
        $this->enableMail();
        $intent = ContactIntent::factory()->stale()->create(['email' => 'ana@example.com', 'created_at' => now()->subHour()]);
        $submission = ContactSubmission::create([...$this->complete(), 'phone' => '55551234', 'email' => 'Ana@example.com']);

        $this->artisan('contact:notify-abandoned')->expectsOutputToContain('Ya enviados por otra vía: 1')->assertSuccessful();

        Mail::assertNothingSent();
        $this->assertSame($submission->id, $intent->fresh()->contact_submission_id);
    }

    public function test_forms_wait_while_email_sending_is_off(): void
    {
        Mail::fake();
        $intent = ContactIntent::factory()->stale()->create();

        $this->artisan('contact:notify-abandoned')->assertSuccessful();

        Mail::assertNothingSent();
        $this->assertNull($intent->fresh()->abandoned_notified_at);
    }

    public function test_the_alert_shows_what_was_typed_and_the_retargeting_cookies(): void
    {
        $intent = ContactIntent::factory()->withTracking()->create(['first_name' => 'Ana', 'last_name' => 'Pérez', 'company' => 'Marca', 'message' => 'Queremos lanzar']);

        $html = (new ContactAbandonedMail($intent))->render();

        $this->assertStringContainsString('Ana Pérez', $html);
        $this->assertStringContainsString('Queremos lanzar', $html);
        $this->assertStringContainsString('fb.1.1700000000000.123456789', $html);
        $this->assertStringContainsString('/admin/contact-intents', $html);
        $this->assertStringContainsString('no lo envió en los últimos 30 minutos', $html);
        $this->assertStringContainsString('No aceptó las cookies de marketing', (new ContactAbandonedMail(ContactIntent::factory()->create()))->render());
    }

    public function test_the_cms_lists_unsent_forms_and_previews_the_alert(): void
    {
        ContactIntent::factory()->withTracking()->create(['first_name' => 'Lucía', 'company' => 'Sin enviar']);
        ContactIntent::factory()->create(['first_name' => 'Pedro', 'submitted_at' => now()]);

        $this->actingAs(User::factory()->create());
        $this->get('/admin/contact-intents')->assertOk()->assertSee('Lucía')->assertSee('fb.1.1700000000000.123456789')->assertDontSee('Pedro');
        $this->get('/admin/correos/vista-previa/sin-completar')->assertOk()->assertSee('Alguien empezó a escribirnos y no terminó.');
        $this->get('/contacto')->assertSee('data-intent-url="'.route('contact.intent').'"', false);
    }

    private string $key = '';

    /**
     * @return array<string, mixed>
     */
    private function partial(): array
    {
        $this->key = $this->key ?: (string) Str::uuid();

        return ['intent_key' => $this->key, 'first_name' => 'Ana', 'email' => 'ana@example.com', 'locale' => 'es', 'market' => 'gt', 'marketing_consent' => false, 'website' => ''];
    }

    /**
     * @return array<string, string>
     */
    private function complete(): array
    {
        return [
            'first_name' => 'Ana', 'last_name' => 'Pérez', 'email' => 'ana@example.com',
            'country_code' => '+502', 'phone' => '5555-1234', 'company' => 'Marca de prueba',
            'job_title' => 'Marketing', 'budget' => 'USD 5,000', 'referral_source' => 'linkedin',
            'message' => 'Necesitamos una estrategia para el lanzamiento de nuestra marca.',
            'locale' => 'es', 'market' => 'gt', 'website' => '',
        ];
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
}
