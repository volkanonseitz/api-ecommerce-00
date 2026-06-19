<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\OrderWalletPoint;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OrderWalletPoint>
 */
class OrderWalletPointFactory extends Factory
{
    protected $model = OrderWalletPoint::class;

    public function definition(): array
    {
        return [
            'amount' => $this->faker->optional(0.8)->randomFloat(2, 1, 100),
            'order_id' => Order::factory(),
        ];
    }

    /**
     * Set a specific amount.
     */
    public function amount(float $amount): static
    {
        return $this->state(fn (array $attributes) => [
            'amount' => $amount,
        ]);
    }

    /**
     * Attach to an existing order.
     */
    public function forOrder(Order $order): static
    {
        return $this->state(fn (array $attributes) => [
            'order_id' => $order->id,
        ]);
    }
}
