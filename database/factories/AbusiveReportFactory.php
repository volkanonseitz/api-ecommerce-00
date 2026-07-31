<?php

namespace Database\Factories;

use App\Models\AbusiveReport;
use App\Models\Question;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AbusiveReport>
 */
class AbusiveReportFactory extends Factory
{
    protected $model = AbusiveReport::class;

    public function definition(): array
    {
        $reportableClass = $this->faker->randomElement([
            Review::class,
            Question::class,
        ]);

        return [
            'user_id' => User::factory(),
            'model_type' => $reportableClass,
            'model_id' => $reportableClass::factory(),
            'message' => $this->faker->sentence(),
        ];
    }

    /**
     * State for reporting a Review.
     */
    public function forReview(): self
    {
        return $this->state(fn (array $attributes) => [
            'model_type' => Review::class,
            'model_id' => Review::factory(),
        ]);
    }

    /**
     * State for reporting a Question.
     */
    public function forQuestion(): self
    {
        return $this->state(fn (array $attributes) => [
            'model_type' => Question::class,
            'model_id' => Question::factory(),
        ]);
    }
}
