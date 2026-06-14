<?php

namespace Database\Factories;

use App\Models\Tax;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Tax>
 */
class TaxFactory extends Factory
{
    protected $model = Tax::class;

    public function definition(): array
    {
        return [
            'country' => $this->faker->optional()->countryCode(),
            'state' => $this->faker->optional()->state(),
            'zip' => $this->faker->optional()->postcode(),
            'city' => $this->faker->optional()->city(),
            'rate' => $this->faker->randomFloat(2, 0, 25),
            'name' => $this->faker->optional()->word(),
            'is_global' => $this->faker->optional()->boolean(),
            'priority' => $this->faker->optional()->numberBetween(1, 10),
            'on_shipping' => $this->faker->boolean(),
        ];
    }
}
