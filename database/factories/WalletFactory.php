<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Wallet;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Wallet>
 */
class WalletFactory extends Factory
{
    protected $model = Wallet::class;

    public function definition(): array
    {
        $total = $this->faker->randomFloat(2, 0, 1000);
        $used = $this->faker->randomFloat(2, 0, $total);

        return [
            'total_points' => $total,
            'points_used' => $used,
            'available_points' => $total - $used,
            'customer_id' => User::factory(),
        ];
    }
}
