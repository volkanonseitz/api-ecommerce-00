<?php

namespace Database\Factories;

use App\Models\Provider;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Provider>
 */
class ProviderFactory extends Factory
{
    protected $model = Provider::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'provider_user_id' => (string) $this->faker->randomNumber(),
            'provider' => $this->faker->randomElement(['google', 'facebook']),
        ];
    }
}
