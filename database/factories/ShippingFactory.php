<?php

namespace Database\Factories;

use App\Models\Shipping;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Shipping>
 */
class ShippingFactory extends Factory
{
    protected $model = Shipping::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->word(),
            'amount' => $this->faker->randomFloat(2, 0, 100),
            'is_global' => $this->faker->boolean(),
            'type' => 'fixed',
        ];
    }
}
