<?php

namespace Database\Factories;

use App\Enums\FlashSaleType;
use App\Models\FlashSale;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Config;

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
            'sale_status' => false,
            'type' => FlashSaleType::PERCENTAGE,
            'rate' => $this->faker->optional()->numberBetween(5, 50),
            'sale_builder' => json_encode([]),
            'image' => json_encode([$this->faker->imageUrl()]),
            'cover_image' => json_encode([$this->faker->imageUrl()]),
            'language' => Config::get('app.locale'),
        ];
    }

    public function active(): static
    {
        return $this->state(fn () => [
            'sale_status' => true,
            'start_date' => now()->subDay(),
            'end_date' => now()->addWeek(),
        ]);
    }
}
