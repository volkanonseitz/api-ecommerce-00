<?php

namespace Database\Factories;

use App\Models\Manufacturer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Manufacturer>
 */
class ManufacturerFactory extends Factory
{
    protected $model = Manufacturer::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->company(),
            'is_approved' => $this->faker->boolean(),
            'image' => json_encode([$this->faker->imageUrl()]),
            'cover_image' => json_encode([$this->faker->imageUrl()]),
            'slug' => $this->faker->unique()->slug(),
            'language' => Config::get('app.locale'),
            'type_id' => Type::factory(),
            'description' => $this->faker->paragraph(),
            'website' => $this->faker->url(),
            'socials' => json_encode(['linkedin' => $this->faker->userName()]),
        ];
    }
}
