<?php

namespace Database\Factories;

use App\Models\Attachment;
use App\Models\DigitalFile;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DigitalFile>
 */
class DigitalFileFactory extends Factory
{
    protected $model = DigitalFile::class;

    public function definition(): array
    {
        $product = Product::query()->inRandomOrder()->first();

        if (! $product) {
            $product = Product::factory()->create([
                'is_digital' => true,
            ]);
        }

        return [
            'attachment_id' => Attachment::query()->value('id') ?? 1,
            'url' => $this->faker->url(),
            'file_name' => $this->faker->word().'.pdf',
            'fileable_type' => Product::class,
            'fileable_id' => $product->id,
        ];
    }

    public function forProduct(int $productId): static
    {
        return $this->state(fn () => [
            'fileable_type' => Product::class,
            'fileable_id' => $productId,
        ]);
    }
}
