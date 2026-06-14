<?php

namespace Database\Factories;

use App\Models\Feedback;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Feedback>
 */
class FeedbackFactory extends Factory
{
    protected $model = Feedback::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'model_type' => 'App\Models\Review',
            'model_id' => Review::factory(),
            'positive' => $this->faker->boolean(),
            'negative' => !$this->faker->boolean(),
        ];
    }
}
