<?php

namespace Database\Factories;

use App\Models\Profile;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Profile>
 */
class ProfileFactory extends Factory
{
    protected $model = Profile::class;

    public function definition(): array
    {
        return [
            'avatar' => json_encode([$this->faker->imageUrl()]),
            'bio' => $this->faker->optional()->paragraph(),
            'socials' => json_encode([
                'twitter' => $this->faker->userName(),
                'facebook' => $this->faker->userName(),
            ]),
            'contact' => $this->faker->optional()->phoneNumber(),
            'notifications' => json_encode(['email' => true]),
            'customer_id' => User::factory(),
        ];
    }
}
