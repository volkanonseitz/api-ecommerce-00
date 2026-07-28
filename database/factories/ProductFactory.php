<?php

namespace Database\Factories;

use App\Enums\ProductVisibilityStatus;
use App\Models\Author;
use App\Models\Manufacturer;
use App\Models\Product;
use App\Models\Shipping;
use App\Models\Shop;
use App\Models\Type;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->words(3, true),
            'slug' => $this->faker->unique()->slug(),
            'description' => $this->faker->paragraph(),
            'type_id' => Type::factory(),
            'price' => $this->faker->randomFloat(2, 10, 500),
            'shop_id' => Shop::factory()->create()->id,
            'author_id' => null,
            'manufacturer_id' => null,
            'is_digital' => $this->faker->boolean(),
            'is_external' => $this->faker->boolean(),
            'external_product_url' => null,
            'external_product_button_text' => null,
            'blocked_dates' => null,
            'sale_price' => $this->faker->optional(0.3)->randomFloat(2, 5, 400),
            'language' => Config::get('app.locale'),
            'min_price' => null,
            'max_price' => null,
            'sku' => Str::upper(Str::random(8)),
            'quantity' => $this->faker->numberBetween(0, 100),
            'sold_quantity' => $this->faker->numberBetween(0, 50),
            'in_stock' => $this->faker->boolean(),
            'is_taxable' => $this->faker->boolean(),
            'in_flash_sale' => $this->faker->boolean(),
            'shipping_class_id' => Shipping::factory(),
            'status' => 'publish', // sesuaikan dengan enum ProductStatus
            'visibility' => ProductVisibilityStatus::VISIBILITY_PUBLIC->value,
            'product_type' => 'simple',
            'unit' => $this->faker->randomElement(['pcs', 'kg', 'm']),
            'height' => $this->faker->optional()->randomFloat(2, 1, 50),
            'width' => $this->faker->optional()->randomFloat(2, 1, 50),
            'length' => $this->faker->optional()->randomFloat(2, 1, 50),
            'image' => json_encode([$this->faker->imageUrl()]),
            'video' => null,
            'gallery' => json_encode([$this->faker->imageUrl()]),
        ];
    }

    public function digital(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_digital' => true,
        ]);
    }

    public function withAuthor(?Author $author = null): static
    {
        return $this->state(fn (array $attributes) => [
            'author_id' => $author->id ?? Author::factory(),
        ]);
    }

    public function withManufacturer(?Manufacturer $manufacturer = null): static
    {
        return $this->state(fn (array $attributes) => [
            'manufacturer_id' => $manufacturer->id ?? Manufacturer::factory(),
        ]);
    }
}
