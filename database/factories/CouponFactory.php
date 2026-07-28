<?php

namespace Database\Factories;

use App\Models\Coupon;
use App\Models\Shop;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;

/**
 * @extends Factory<Coupon>
 */
class CouponFactory extends Factory
{
    protected $model = Coupon::class;

    public function definition(): array
    {
        return [
            'code' => Str::upper(Str::random(8)),
            'language' => Config::get('app.locale'),
            'description' => $this->faker->sentence(),
            'image' => json_encode([$this->faker->imageUrl()]),
            'type' => 'fixed',
            'amount' => $this->faker->randomFloat(2, 5, 50),
            'minimum_cart_amount' => $this->faker->randomFloat(2, 0, 100),
            'active_from' => now()->subDays(1)->format('Y-m-d H:i:s'),
            'expire_at' => now()->addDays(30)->format('Y-m-d H:i:s'),
            'target' => $this->faker->boolean(),
            'is_approve' => $this->faker->boolean(80),
            'shop_id' => Shop::factory()->create()->id,
            'user_id' => null,
        ];
    }
}
