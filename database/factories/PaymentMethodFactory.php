<?php

namespace Database\Factories;

use App\Models\PaymentMethod;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PaymentMethod>
 */
class PaymentMethodFactory extends Factory
{
    protected $model = PaymentMethod::class;

    public function definition(): array
    {
        return [
            'method_key' => Str::random(16),
            'payment_gateway_id' => PaymentGateway::factory(),
            'default_card' => $this->faker->boolean(),
            'fingerprint' => md5($this->faker->uuid()),
            'owner_name' => $this->faker->name(),
            'network' => $this->faker->randomElement(['Visa', 'Mastercard']),
            'type' => 'credit',
            'last4' => $this->faker->numerify('####'),
            'expires' => $this->faker->creditCardExpirationDateString(),
            'origin' => $this->faker->optional()->country(),
            'verification_check' => $this->faker->optional()->word(),
        ];
    }
}
