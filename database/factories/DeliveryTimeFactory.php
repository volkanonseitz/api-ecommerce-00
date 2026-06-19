<?php

namespace Database\Factories;

use App\Models\DeliveryTime;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DeliveryTime>
 */
class DeliveryTimeFactory extends Factory
{
    protected $model = DeliveryTime::class;

    public function definition(): array
    {
        $title = $this->faker->unique()->word();

        return [
            'title' => $title,
            'slug' => null,
            'icon' => $this->faker->imageUrl(),
            'description' => $this->faker->optional()->sentence(),
            'language' => Config::get('app.locale'),
        ];
    }
}
