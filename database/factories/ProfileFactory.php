<?php

namespace Database\Factories;

use App\Models\Profile;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Profile>
 */
class ProfileFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'avatar' => [
                'url' => fake()->imageUrl(),
                'filename' => fake()->uuid() . '.jpg',
            ],

            'bio' => fake()->paragraph(),

            'socials' => [
                'instagram' => fake()->userName(),
                'facebook' => fake()->userName(),
                'twitter' => fake()->userName(),
            ],

            'contact' => fake()->phoneNumber(),

            'notifications' => [
                'email' => true,
                'sms' => false,
                'push' => true,
            ],

            'customer_id' => User::factory(),
        ];
    }
}
