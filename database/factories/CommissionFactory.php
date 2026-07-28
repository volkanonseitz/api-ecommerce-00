<?php

namespace Database\Factories;

use App\Models\Commission;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Config;

/**
 * @extends Factory<Commission>
 */
class CommissionFactory extends Factory
{
    protected $model = Commission::class;

    public function definition(): array
    {
        return [
            'level' => $this->faker->randomElement(['bronze', 'silver', 'gold']),
            'sub_level' => $this->faker->word(), // jangan pakai optional()
            'description' => $this->faker->paragraph(),
            'min_balance' => $this->faker->numberBetween(0, 1000),
            'max_balance' => (string) $this->faker->numberBetween(1000, 100000),
            'commission' => $this->faker->randomFloat(2, 1, 30),
            'image' => json_encode([$this->faker->imageUrl()]),
            'language' => Config::get('app.locale'),
        ];
    }
}
