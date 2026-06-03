<?php

namespace Database\Factories;

use App\Models\Wallet;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Wallet>
 */
class WalletFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $total = fake()->numberBetween(100, 10000);
        $used = fake()->numberBetween(0, $total);

        return [
            'total_points' => $total,
            'points_used' => $used,
            'available_points' => $total - $used,
            'customer_id' => User::factory(),
        ];
    }
}
