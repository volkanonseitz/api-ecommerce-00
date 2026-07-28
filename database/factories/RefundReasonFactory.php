<?php

namespace Database\Factories;

use App\Models\RefundReason;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Config;

/**
 * @extends Factory<RefundReason>
 */
class RefundReasonFactory extends Factory
{
    protected $model = RefundReason::class;

    public function definition(): array
    {
        $name = $this->faker->unique()->sentence(3);

        return [
            'name' => $name,
            'slug' => null,
            'language' => Config::get('app.locale'),
        ];
    }
}
