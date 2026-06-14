<?php

namespace Database\Factories;

use App\Models\Balance;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Balance>
 */
class BalanceFactory extends Factory
{
    protected $model = Balance::class;

    public function definition(): array
    {
        $total = $this->faker->randomFloat(2, 0, 10000);
        $withdrawn = $this->faker->randomFloat(2, 0, $total);
        return [
            'shop_id' => Shop::factory(),
            'admin_commission_rate' => $this->faker->optional()->randomFloat(2, 5, 20),
            'total_earnings' => $total,
            'withdrawn_amount' => $withdrawn,
            'current_balance' => $total - $withdrawn,
            'is_custom_commission' => $this->faker->boolean(),
            'payment_info' => json_encode(['bank' => $this->faker->company()]),
        ];
    }
}
