<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\Question;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Question>
 */
class QuestionFactory extends Factory
{
    protected $model = Question::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'shop_id' => Shop::factory()->create()->id,
            'product_id' => Product::factory(),
            'question' => $this->faker->sentence(),
            'answer' => $this->faker->optional()->paragraph(),
        ];
    }
}
