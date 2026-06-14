<?php

namespace Database\Factories;

use App\Models\Type;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Type>
 */
class TypeFactory extends Factory
{
    protected $model = Type::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->word(),
            'settings' => json_encode([]),
            'slug' => $this->faker->unique()->slug(),
            'language' => Config::get('app.locale'),
            'icon' => $this->faker->optional()->imageUrl(),
            'promotional_sliders' => json_encode([$this->faker->imageUrl()]),
            'images' => json_encode([$this->faker->imageUrl()]),
        ];
    }
}
