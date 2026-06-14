<?php

namespace Database\Factories;

use App\Models\AttributeValue;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AttributeValue>
 */
class AttributeValueFactory extends Factory
{
    protected $model = AttributeValue::class;

    public function definition(): array
    {
        $value = $this->faker->unique()->word();
        return [
            'slug' => Str::slug($value),
            'attribute_id' => Attribute::factory(),
            'value' => $value,
            'language' => Config::get('app.locale'),
            'meta' => $this->faker->optional()->sentence(),
        ];
    }
}
