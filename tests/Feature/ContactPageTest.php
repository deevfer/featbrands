<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class ContactPageTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_contact_page_has_all_requested_fields_and_its_own_active_navigation(): void
    {
        $response = $this->get('/contacto')->assertOk()->assertViewIs('site.contact')
            ->assertSeeText('Tu próximo paso')
            ->assertSee('data-nav-page="contact"', false)
            ->assertSee('aria-current="page"', false);

        foreach (['first_name', 'last_name', 'email', 'country_code', 'phone', 'company', 'job_title', 'budget', 'referral_source', 'message'] as $field) {
            $response->assertSee('name="'.$field.'"', false);
        }

        $this->get('/contacto?lang=en&market=us')->assertOk()
            ->assertSeeText('Your next step')
            ->assertSeeText('How did you hear about Feat.?')
            ->assertSee('value="+1"', false)
            ->assertSee(route('contact', ['lang' => 'es', 'market' => 'us']));
    }

    public function test_valid_request_is_saved_with_normalized_phone_and_only_permitted_fields(): void
    {
        $this->postJson('/contacto', [...$this->payload(), 'id' => 999, 'created_at' => '2000-01-01'])
            ->assertCreated()->assertJsonStructure(['message']);

        $this->assertDatabaseCount('contact_submissions', 1);
        $this->assertDatabaseHas('contact_submissions', [
            'first_name' => 'Ana', 'last_name' => 'Pérez', 'email' => 'ana@example.com',
            'phone' => '55551234', 'country_code' => '+502', 'company' => 'Marca de prueba',
            'job_title' => 'Marketing', 'budget' => 'USD 5,000', 'referral_source' => 'linkedin',
            'message' => 'Necesitamos una estrategia para el lanzamiento de nuestra marca.',
            'locale' => 'es', 'market' => 'gt',
        ]);
        $this->assertDatabaseMissing('contact_submissions', ['id' => 999]);
        $this->assertDatabaseMissing('contact_submissions', ['created_at' => '2000-01-01']);
    }

    public function test_form_without_javascript_redirects_back_to_contact_with_confirmation(): void
    {
        $this->post('/contacto', [...$this->payload(), 'locale' => 'en', 'market' => 'cr'])
            ->assertRedirect(route('contact', ['lang' => 'en', 'market' => 'cr']))
            ->assertSessionHas('contact_submitted', true);

        $this->assertDatabaseHas('contact_submissions', ['locale' => 'en', 'market' => 'cr']);
    }

    public function test_optional_budget_and_job_title_do_not_block_a_request(): void
    {
        $this->postJson('/contacto', [...$this->payload(), 'budget' => '', 'job_title' => ''])
            ->assertCreated();

        $this->assertDatabaseHas('contact_submissions', ['budget' => null, 'job_title' => null]);
    }

    public function test_country_selector_contains_worldwide_options_and_localized_names(): void
    {
        $this->get('/contacto')->assertOk()
            ->assertSeeText('Guatemala 🇬🇹 +502')
            ->assertSeeText('Canadá 🇨🇦 +1')
            ->assertSeeText('Estados Unidos 🇺🇸 +1')
            ->assertSeeText('Japón 🇯🇵 +81')
            ->assertSeeText('Sudáfrica 🇿🇦 +27')
            ->assertSeeText('Australia 🇦🇺 +61')
            ->assertSeeText('Kosovo 🇽🇰 +383');

        $this->get('/contacto?lang=en')->assertOk()
            ->assertSeeText('Japan 🇯🇵 +81')
            ->assertSeeText('South Africa 🇿🇦 +27');
    }

    public function test_country_selection_determines_the_prefix_without_javascript(): void
    {
        $this->post('/contacto', [...$this->payload(), 'phone_country' => 'JP'])
            ->assertRedirect();

        $this->assertDatabaseHas('contact_submissions', ['country_code' => '+81', 'phone' => '55551234']);
    }

    public function test_country_with_short_local_number_can_submit(): void
    {
        $this->postJson('/contacto', [...$this->payload(), 'phone_country' => 'NU', 'phone' => '1234'])
            ->assertCreated();

        $this->assertDatabaseHas('contact_submissions', ['country_code' => '+683', 'phone' => '1234']);
    }

    public function test_country_identity_survives_validation_when_dialing_codes_are_shared(): void
    {
        $this->followingRedirects()->from('/contacto')
            ->post('/contacto', [...$this->payload(), 'phone_country' => 'CA', 'email' => 'invalid'])
            ->assertOk()
            ->assertSee('value="CA" data-dial-code="+1" data-flag="🇨🇦" selected', false);
    }

    public function test_required_fields_are_validated_without_saving_a_request(): void
    {
        $this->postJson('/contacto', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['first_name', 'last_name', 'email', 'country_code', 'phone', 'company', 'referral_source', 'message', 'locale', 'market']);

        $this->assertDatabaseCount('contact_submissions', 0);
    }

    /**
     * @return array<string, array{string, mixed}>
     */
    public static function invalidFields(): array
    {
        return [
            'email format' => ['email', 'invalid-email'],
            'country prefix' => ['country_code', '502'],
            'unknown country' => ['phone_country', 'XX'],
            'malformed country' => ['phone_country', ['GT']],
            'phone characters' => ['phone', 'call-me-now'],
            'phone length' => ['phone', '123456789012345'],
            'short message' => ['message', 'Hola'],
            'unknown referral' => ['referral_source', 'unlisted'],
            'invalid locale' => ['locale', 'fr'],
            'invalid market' => ['market', 'mx'],
            'malformed phone' => ['phone', ['55551234']],
            'honeypot' => ['website', 'https://spam.example'],
        ];
    }

    #[DataProvider('invalidFields')]
    public function test_invalid_field_is_rejected_without_saving(string $field, mixed $value): void
    {
        $this->postJson('/contacto', [...$this->payload(), $field => $value])
            ->assertUnprocessable()->assertJsonValidationErrors($field);

        $this->assertDatabaseCount('contact_submissions', 0);
    }

    public function test_english_validation_message_matches_the_selected_language(): void
    {
        $this->postJson('/contacto', [...$this->payload(), 'locale' => 'en', 'email' => 'invalid'])
            ->assertUnprocessable()
            ->assertJsonPath('errors.email.0', 'Enter a valid email address.');
    }

    public function test_repeated_attempts_are_rate_limited(): void
    {
        for ($attempt = 0; $attempt < 6; $attempt++) {
            $this->postJson('/contacto', [])->assertUnprocessable();
        }

        $this->postJson('/contacto', $this->payload())->assertTooManyRequests();
        $this->assertDatabaseCount('contact_submissions', 0);
    }

    public function test_submissions_require_admin_authentication_and_escape_submitted_content(): void
    {
        $this->get('/admin/contact-submissions')->assertRedirect('/admin/login');

        $this->postJson('/contacto', [...$this->payload(),
            'first_name' => '<script>alert(1)</script>',
            'company' => '<img src=x onerror=alert(1)>',
            'message' => '<script>alert("solicitud")</script>',
        ])->assertCreated();

        config(['app.env' => 'local']);
        $this->actingAs(User::factory()->create())
            ->get('/admin/contact-submissions')->assertOk()
            ->assertSeeText('Solicitudes de contacto')
            ->assertSee('<script>alert(1)</script>')
            ->assertDontSee('<script>alert(1)</script>', false)
            ->assertSee('<img src=x onerror=alert(1)>')
            ->assertDontSee('<img src=x onerror=alert(1)>', false)
            ->assertSee('<script>alert("solicitud")</script>')
            ->assertDontSee('<script>alert("solicitud")</script>', false);
    }

    public function test_native_validation_returns_the_draft_for_correction(): void
    {
        $this->followingRedirects()->from('/contacto?lang=es&market=gt')
            ->post('/contacto', [...$this->payload(), 'email' => 'invalid'])
            ->assertOk()
            ->assertSee('value="Ana"', false)
            ->assertSeeText('Ingresa un email válido.');
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
