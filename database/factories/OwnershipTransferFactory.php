<?php

namespace Database\Factories;

use App\Enums\DefaultStatusType;
use App\Models\OwnershipTransfer;
use App\Models\Shop;
use App\Models\User;
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
            'transaction_identifier' => null,
            'from' => User::factory(),
            'shop_id' => Shop::factory(),
            'to' => User::factory(),
            'message' => $this->faker->optional()->sentence(),
            'created_by' => User::factory(),
            'status' => DefaultStatusType::PENDING,
        ];
    }
}
