<?php

namespace Database\Factories;

use App\Models\Profile;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Profile>
 */
class ShopFactory extends Factory
{
    protected $model = Shop::class;

    public function definition(): array
    {
        return [
            'owner_id' => User::factory(),
            'name' => $this->faker->company(),
            'slug' => null, // akan di-generate oleh boot
            'description' => $this->faker->paragraph(),
            'cover_image' => json_encode([$this->faker->imageUrl()]),
            'logo' => json_encode([$this->faker->imageUrl()]),
            'is_active' => $this->faker->boolean(),
            'address' => json_encode([
                'street' => $this->faker->streetAddress(),
                'city' => $this->faker->city(),
                'country' => $this->faker->country(),
            ]),
            'settings' => json_encode(['currency' => 'USD']),
            'notifications' => json_encode(['email' => $this->faker->email()]),
        ];
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
        ]);
    }
}
