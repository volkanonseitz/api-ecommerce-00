<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Config;

/**
 * @extends Factory<Model>
 */
class AvailabilityFactory extends Factory
{
    protected $model = Availability::class;

    public function definition(): array
    {
        return [
            'from' => $this->faker->dateTimeBetween('now', '+1 week')->format('Y-m-d H:i:s'),
            'to' => $this->faker->dateTimeBetween('+1 week', '+2 weeks')->format('Y-m-d H:i:s'),
            'language' => Config::get('app.locale'),
            'booking_duration' => '2 hours',
            'order_quantity' => $this->faker->numberBetween(1, 5),
            'bookable_type' => 'App\Models\Product',
            'bookable_id' => Product::factory(),
            'order_id' => null,
            'product_id' => null,
        ];
    }
}
