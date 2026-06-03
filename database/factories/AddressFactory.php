<?php

namespace Database\Factories;

use App\Models\Address;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Address>
 */
class AddressFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->randomElement([
                'Rumah',
                'Kantor',
                'Gudang',
            ]),

            'type' => fake()->randomElement([
                'home',
                'office',
                'warehouse',
            ]),

            'default' => false,

            'address' => [
                'street' => fake()->streetAddress(),
                'city' => fake()->city(),
                'province' => fake()->state(),
                'postal_code' => fake()->postcode(),
                'country' => 'Indonesia',
            ],

            'location' => [
                'lat' => fake()->latitude(),
                'lng' => fake()->longitude(),
            ],

            'customer_id' => User::factory(),
        ];
    }
}
