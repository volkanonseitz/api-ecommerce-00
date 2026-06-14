<?php

namespace Database\Factories;

use App\Models\Address;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Address>
 */
class AddressFactory extends Factory
{
    protected $model = Address::class;

    public function definition(): array
    {
        return [
            'title' => $this->faker->word(),
            'type' => 'shipping',
            'default' => $this->faker->boolean(20),
            'address' => json_encode(['street' => $this->faker->streetAddress()]),
            'location' => json_encode(['lat' => $this->faker->latitude(), 'lng' => $this->faker->longitude()]),
            'customer_id' => User::factory(),
        ];
    }
}
