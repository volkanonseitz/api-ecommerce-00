<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Category>
 */
class CategoryFactory extends Factory
{
    protected $model = Category::class;

    public function definition(): array
    {
        $name = $this->faker->unique()->word();
        return [
            'name' => $name,
            'slug' => null, // auto by boot
            'language' => $this->faker->languageCode(),
            'icon' => $this->faker->optional()->imageUrl(),
            'image' => json_encode([$this->faker->imageUrl()]),
            'banner_image' => json_encode([$this->faker->imageUrl()]),
            'details' => $this->faker->optional()->paragraph(),
            'parent' => null,
        ];
    }

    public function withParent(?Category $parent = null): static
    {
        return $this->state(fn (array $attributes) => [
            'parent' => $parent ? $parent->id : Category::factory(),
        ]);
    }
}
