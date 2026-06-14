<?php

namespace Database\Factories;

use App\Models\Resource;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Resource>
 */
class ResourceFactory extends Factory
{
    protected $model = Resource::class;

    public function definition(): array
    {
        $name = $this->faker->unique()->word();
        return [
            'name' => $name,
            'slug' => null,
            'language' => Config::get('app.locale'),
            'icon' => $this->faker->optional()->imageUrl(),
            'details' => $this->faker->optional()->paragraph(),
            'image' => json_encode([$this->faker->imageUrl()]),
            'is_approved' => $this->faker->boolean(80),
            'price' => $this->faker->optional()->randomFloat(2, 10, 500),
            'type' => 'dropoff',
        ];
    }
}
