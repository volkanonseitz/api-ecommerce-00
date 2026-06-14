<?php

namespace Database\Factories;

use App\Models\Tag;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Tag>
 */
class TagFactory extends Factory
{
    protected $model = Tag::class;

    public function definition(): array
    {
        $name = $this->faker->unique()->word();
        return [
            'name' => $name,
            'slug' => null,
            'language' => Config::get('app.locale'),
            'icon' => $this->faker->optional()->imageUrl(),
            'image' => json_encode([$this->faker->imageUrl()]),
            'details' => $this->faker->optional()->paragraph(),
        ];
    }
}
