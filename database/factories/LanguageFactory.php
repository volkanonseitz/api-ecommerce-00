<?php

namespace Database\Factories;

use App\Models\Language;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Language>
 */
class LanguageFactory extends Factory
{
    protected $model = Language::class;

    public function definition(): array
    {
        return [
            'flag' => json_encode([$this->faker->imageUrl()]),
            'language_code' => $this->faker->unique()->languageCode(),
            'language_name' => $this->faker->name(),
        ];
    }
}
