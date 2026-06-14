<?php

namespace Database\Factories;

use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition(): array
    {
        return [
            'tracking_number' => Str::random(12),
            'customer_id' => User::factory(),
            'customer_contact' => $this->faker->phoneNumber(),
            'customer_name' => $this->faker->name(),
            'amount' => $this->faker->randomFloat(2, 20, 1000),
            'sales_tax' => $this->faker->randomFloat(2, 0, 100),
            'paid_total' => $this->faker->randomFloat(2, 20, 1000),
            'total' => $this->faker->randomFloat(2, 20, 1000),
            'note' => $this->faker->optional()->sentence(),
            'language' => Config::get('app.locale'),
            'cancelled_amount' => 0,
            'cancelled_tax' => 0,
            'cancelled_delivery_fee' => 0,
            'coupon_id' => null,
            'parent_id' => null,
            'shop_id' => Shop::factory(),
            'discount' => $this->faker->optional(0.3)->randomFloat(2, 5, 50),
            'payment_gateway' => $this->faker->randomElement(['stripe', 'paypal']),
            'altered_payment_gateway' => null,
            'shipping_address' => json_encode(['address' => $this->faker->streetAddress()]),
            'billing_address' => json_encode(['address' => $this->faker->streetAddress()]),
            'logistics_provider' => null,
            'delivery_fee' => $this->faker->randomFloat(2, 0, 50),
            'delivery_time' => $this->faker->optional()->dateTimeBetween('now', '+1 week')->format('Y-m-d H:i:s'),
            'order_status' => 'pending',
            'payment_status' => 'pending',
        ];
    }
}
