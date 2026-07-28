<?php

namespace Database\Factories;

use App\Models\TermsAndConditions;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Config;

/**
 * @extends Factory<TermsAndConditions>
 */
class TermsAndConditionsFactory extends Factory
{
    protected $model = TermsAndConditions::class;

    public function definition(): array
    {
        $title = $this->faker->sentence();

        return [
            'user_id' => User::factory(),
            'shop_id' => null,
            'title' => $title,
            'slug' => null,
            'description' => $this->faker->paragraph(),
            'type' => $this->faker->randomElement(['general', 'vendor']),
            'issued_by' => $this->faker->name(),
            'is_approved' => true,
            'language' => Config::get('app.locale'),
        ];
    }

    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_approved' => true,
        ]);
    }
}
