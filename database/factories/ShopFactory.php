<?php

namespace Database\Factories;

use App\Models\Shop;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ShopFactory extends Factory
{
    protected $model = Shop::class;

    public function definition(): array
    {
        $name = fake()->unique()->company();

        return [
            'name' => $name,
            'slug' => null, // dibentuk otomatis oleh model
            'description' => fake()->paragraph(),

            'cover_image' => [
                'id' => fake()->randomNumber(),
                'original_url' => fake()->imageUrl(),
                'thumbnail_url' => fake()->imageUrl(100, 100),
            ],

            'logo' => [
                'id' => fake()->randomNumber(),
                'original_url' => fake()->imageUrl(),
                'thumbnail_url' => fake()->imageUrl(50, 50),
            ],

            'address' => [
                'street_address' => fake()->streetAddress(),
                'city' => fake()->city(),
                'state' => fake()->state(),
                'zip' => fake()->postcode(),
                'country' => fake()->country(),
                'latitude' => fake()->latitude(),
                'longitude' => fake()->longitude(),
            ],

            'settings' => [
                'contact_number' => fake()->phoneNumber(),
                'website' => fake()->domainName(),
                'external_id' => fake()->uuid(),
                'socials' => [
                    ['url' => fake()->url(), 'icon' => 'facebook'],
                ],
                'currency' => 'IDR',
            ],

            'notifications' => [
                'new_order' => true,
                'status_change' => true,
            ],
            'owner_id' => User::factory(),
        ];
    }

    public function active(): static
    {
        return $this->state([]);
    }

    public function inactive(): static
    {
        return $this->state([]);
    }
}
