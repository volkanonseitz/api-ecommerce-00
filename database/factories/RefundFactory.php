<?php

namespace Database\Factories;

use App\Models\Refund;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Refund>
 */
class RefundFactory extends Factory
{
    protected $model = Refund::class;

    public function definition(): array
    {
        return [
            'amount' => $this->faker->randomFloat(2, 5, 500),
            'status' => RefundStatus::PENDING,
            'title' => $this->faker->sentence(),
            'description' => $this->faker->paragraph(),
            'images' => json_encode([$this->faker->imageUrl()]),
            'order_id' => Order::factory(),
            'customer_id' => User::factory(),
            'refund_policy_id' => RefundPolicy::factory(),
            'shop_id' => Shop::factory(),
            'refund_reason_id' => RefundReason::factory(),
        ];
    }
}
