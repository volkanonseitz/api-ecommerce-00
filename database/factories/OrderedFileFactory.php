<?php

namespace Database\Factories;

use App\Models\OrderedFile;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OrderedFile>
 */
class OrderedFileFactory extends Factory
{
    protected $model = OrderedFile::class;

    public function definition(): array
    {
        return [
            'purchase_key' => Str::random(32),
            'digital_file_id' => DigitalFile::factory(),
            'tracking_number' => Order::factory()->create()->tracking_number,
            'customer_id' => User::factory(),
        ];
    }
}
