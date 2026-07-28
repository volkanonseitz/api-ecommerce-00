<?php

namespace Database\Factories;

use App\Models\StoreNotice;
use App\Models\StoreNoticeRead;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StoreNoticeRead>
 */
class StoreNoticeReadFactory extends Factory
{
    protected $model = StoreNoticeRead::class;

    public function definition(): array
    {
        return [
            'store_notice_id' => StoreNotice::factory(),
            'user_id' => User::factory(),
            'is_read' => $this->faker->boolean(),
        ];
    }
}
