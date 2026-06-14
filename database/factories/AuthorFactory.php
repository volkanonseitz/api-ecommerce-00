<?php

namespace Database\Factories;

use App\Models\Author;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Author>
 */
class AuthorFactory extends Factory
{
    protected $model = Author::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'is_approved' => $this->faker->boolean(),
            'image' => json_encode([$this->faker->imageUrl()]),
            'cover_image' => json_encode([$this->faker->imageUrl()]),
            'slug' => $this->faker->unique()->slug(),
            'language' => Config::get('app.locale'),
            'bio' => $this->faker->paragraph(),
            'quote' => $this->faker->sentence(),
            'born' => $this->faker->optional()->year(),
            'death' => $this->faker->optional()->year(),
            'languages' => $this->faker->languageCode(),
            'socials' => json_encode(['twitter' => $this->faker->userName()]),
        ];
    }
}

