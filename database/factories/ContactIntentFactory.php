<?php

namespace Database\Factories;

use App\Models\ContactIntent;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<ContactIntent>
 */
class ContactIntentFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'intent_key' => (string) Str::uuid(),
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'email' => fake()->unique()->safeEmail(),
            'country_code' => '+502',
            'phone' => '55551234',
            'company' => fake()->company(),
            'message' => null,
            'locale' => 'es',
            'market' => 'gt',
            'marketing_consent' => false,
            'tracking' => null,
            'last_activity_at' => now(),
        ];
    }

    /**
     * Last edited long enough ago to count as abandoned.
     */
    public function stale(): static
    {
        return $this->state(fn (): array => ['last_activity_at' => now()->subMinutes(ContactIntent::ABANDONED_AFTER_MINUTES + 5)]);
    }

    /**
     * Visitor accepted marketing cookies, so their ad identifiers were kept.
     */
    public function withTracking(): static
    {
        return $this->state(fn (): array => [
            'marketing_consent' => true,
            'tracking' => ['fbp' => 'fb.1.1700000000000.123456789', 'ga' => 'GA1.1.123456789.1700000000'],
        ]);
    }
}
