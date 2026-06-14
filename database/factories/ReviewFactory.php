<?php

namespace Database\Factories;

use App\Models\Review;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Review>
 */
class ReviewFactory extends Factory
{
    protected $model = Review::class;

    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'user_id' => User::factory(),
            'shop_id' => Shop::factory(),
            'product_id' => Product::factory(),
            'variation_option_id' => null,
            'comment' => $this->faker->paragraph(),
            'rating' => $this->faker->randomFloat(1, 1, 5),
            'photos' => json_encode([$this->faker->imageUrl()]),
        ];
    }
}
