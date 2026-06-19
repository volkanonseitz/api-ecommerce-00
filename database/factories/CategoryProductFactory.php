<?php

namespace Database\Factories;

use App\Models\CategoryProduct;
use App\Models\Model;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Model>
 */
class CategoryProductFactory extends Factory
{
    protected $model = CategoryProduct::class;

    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'category_id' => Category::factory(),
        ];
    }
}
