<?php

namespace Database\Factories;

use App\Models\OwnershipTransfer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OwnershipTransfer>
 */
class OwnershipTransferFactory extends Factory
{
    protected $model = OwnershipTransfer::class;

    public function definition(): array
    {
        $from = User::factory()->create();
        $to = User::factory()->create();
        $shop = Shop::factory()->create(['owner_id' => $from->id]);

        return [
            'transaction_identifier' => '2026-06-14-0001', // akan dioverride boot
            'from' => $from->id,
            'shop_id' => $shop->id,
            'to' => $to->id,
            'message' => $this->faker->optional()->sentence(),
            'created_by' => User::factory()->create()->id,
            'status' => DefaultStatusType::PENDING,
        ];
    }
}
