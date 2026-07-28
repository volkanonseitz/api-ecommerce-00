<?php

namespace Database\Factories;

use App\Models\DigitalFile;
use App\Models\Order;
use App\Models\OrderedFile;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class OrderedFileFactory extends Factory
{
    protected $model = OrderedFile::class;

    public function definition(): array
    {
        $digitalFile = DigitalFile::query()->inRandomOrder()->first();

        if (! $digitalFile) {
            $product = Product::factory()->create([
                'is_digital' => true,
            ]);

            $digitalFile = DigitalFile::factory()
                ->forProduct($product->id)
                ->create();
        }

        $order = Order::factory()->create();

        return [
            'purchase_key' => Str::random(32),
            'digital_file_id' => $digitalFile->id,
            'tracking_number' => $order->tracking_number,
            'customer_id' => User::factory(),
        ];
    }
}
