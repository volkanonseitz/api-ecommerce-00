<?php

namespace Database\Factories;

use App\Models\TermsAndConditions;
use Illuminate\Database\Eloquent\Factories\Factory;

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
            'is_approved' => $this->faker->boolean(80),
            'language' => Config::get('app.locale'),
        ];
    }
}
