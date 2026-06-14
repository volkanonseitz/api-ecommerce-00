<?php

namespace Database\Factories;

use App\Models\Variation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Variation>
 */
class VariationFactory extends Factory
{
    protected $model = Variation::class;

    public function definition(): array
    {
        return [
            'title' => $this->faker->word(),
            'image' => json_encode([$this->faker->imageUrl()]),
            'is_digital' => $this->faker->boolean(),
            'price' => $this->faker->randomFloat(2, 5, 200),
            'sale_price' => $this->faker->optional(0.3)->randomFloat(2, 3, 150),
            'language' => Config::get('app.locale'),
            'quantity' => $this->faker->numberBetween(1, 50),
            'sold_quantity' => $this->faker->numberBetween(0, 30),
            'is_disable' => $this->faker->boolean(),
            'sku' => Str::upper(Str::random(8)),
            'options' => json_encode(['color' => $this->faker->safeColorName()]),
            'product_id' => Product::factory(),
        ];
    }
}
