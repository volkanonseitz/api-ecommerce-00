<?php

namespace Database\Factories;

use App\Models\Shop;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Shop>
 */
class ShopFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->company();

        return [
            'owner_id' => User::factory(),

            'name' => $name,

            'slug' => Str::slug($name . '-' . fake()->unique()->numberBetween(1, 9999)),

            'description' => fake()->paragraph(),

            'cover_image' => [
                'url' => fake()->imageUrl(1200, 400),
            ],

            'logo' => [
                'url' => fake()->imageUrl(300, 300),
            ],

            'is_active' => true,

            'address' => [
                'street' => fake()->streetAddress(),
                'city' => fake()->city(),
                'province' => fake()->state(),
            ],

            'settings' => [
                'currency' => 'IDR',
                'timezone' => 'Asia/Jakarta',
            ],

            'notifications' => [
                'email_order' => true,
                'email_promo' => false,
            ],
        ];
    }
}
