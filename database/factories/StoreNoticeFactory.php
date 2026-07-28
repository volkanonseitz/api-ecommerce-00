<?php

namespace Database\Factories;

use App\Enums\StoreNoticePriority;
use App\Enums\StoreNoticeType;
use App\Models\StoreNotice;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StoreNotice>
 */
class StoreNoticeFactory extends Factory
{
    protected $model = StoreNotice::class;

    public function definition(): array
    {
        $effectiveFrom = $this->faker->dateTimeBetween('-1 week', 'now');

        return [
            'priority' => StoreNoticePriority::LOW,
            'notice' => $this->faker->sentence(),
            'description' => $this->faker->optional()->paragraph(),
            'effective_from' => $effectiveFrom,
            'expired_at' => $this->faker->dateTimeBetween($effectiveFrom, '+1 month'),
            'type' => StoreNoticeType::ALL_VENDOR,
            'created_by' => User::factory(),
            'updated_by' => null,
        ];
    }
}
