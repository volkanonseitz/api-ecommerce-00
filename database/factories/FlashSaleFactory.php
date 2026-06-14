<?php

namespace Database\Factories;

use App\Models\FlashSale;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FlashSale>
 */
class FlashSaleFactory extends Factory
{
    protected $model = FlashSale::class;

    public function definition(): array
    {
        $startDate = $this->faker->dateTimeBetween('now', '+1 week');
        return [
            'title' => $this->faker->sentence(),
            'slug' => null,
            'description' => $this->faker->optional()->paragraph(),
            'start_date' => $startDate,
            'end_date' => $this->faker->dateTimeBetween($startDate, '+2 weeks'),
            'sale_status' => $this->faker->boolean(),
            'type' => FlashSaleType::PERCENTAGE,
            'rate' => $this->faker->optional()->numberBetween(5, 50),
            'sale_builder' => json_encode([]),
            'image' => json_encode([$this->faker->imageUrl()]),
            'cover_image' => json_encode([$this->faker->imageUrl()]),
            'language' => Config::get('app.locale'),
        ];
    }
}
