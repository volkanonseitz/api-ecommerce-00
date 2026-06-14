<?php

namespace Database\Factories;

use App\Models\PaymentIntent;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PaymentIntent>
 */
class PaymentIntentFactory extends Factory
{
    protected $model = PaymentIntent::class;

    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'tracking_number' => fn (array $attrs) => Order::find($attrs['order_id'])?->tracking_number ?? Str::random(12),
            'payment_gateway' => $this->faker->randomElement(['stripe', 'paypal']),
            'payment_intent_info' => json_encode(['intent_id' => 'pi_' . Str::random(24)]),
        ];
    }
}
