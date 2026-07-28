<?php

namespace Database\Factories;

use App\Enums\WithdrawStatus;
use App\Models\Shop;
use App\Models\Withdraw;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Withdraw>
 */
class WithdrawFactory extends Factory
{
    protected $model = Withdraw::class;

    public function definition(): array
    {
        return [
            'shop_id' => Shop::factory()->create()->id,
            'amount' => $this->faker->randomFloat(2, 10, 1000),
            'payment_method' => 'bank_transfer',
            'status' => WithdrawStatus::PENDING,
            'details' => $this->faker->optional()->sentence(),
            'note' => $this->faker->optional()->sentence(),
        ];
    }
}
