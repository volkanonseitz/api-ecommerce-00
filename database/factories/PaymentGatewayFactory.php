<?php

namespace Database\Factories;

use App\Models\Model;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Model>
 */
class PaymentGatewayFactory extends Factory
{
    protected $model = PaymentGateway::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'customer_id' => (string) $this->faker->randomNumber(),
            'gateway_name' => $this->faker->randomElement(['stripe', 'paypal']),
        ];
    }
}
