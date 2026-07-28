<?php

namespace Database\Factories;

use App\Models\Banner;
use App\Models\Model;
use App\Models\Type;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Model>
 */
class BannerFactory extends Factory
{
    protected $model = Banner::class;

    public function definition(): array
    {
        return [
            'type_id' => Type::factory(),
            'title' => $this->faker->sentence(),
            'description' => $this->faker->optional()->paragraph(),
            'image' => json_encode([$this->faker->imageUrl()]),
        ];
    }
}
