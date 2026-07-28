<?php

namespace Database\Factories;

use App\Models\FlashSale;
use App\Models\FlashSaleRequest;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Config;

class FlashSaleRequestFactory extends Factory
{
    protected $model = FlashSaleRequest::class;

    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence(3),
            'flash_sale_id' => FlashSale::factory(),
            'requested_product_ids' => [],
            'request_status' => false,
            'note' => $this->faker->optional()->sentence(),
            'language' => Config::get('app.locale'),
        ];
    }
}
