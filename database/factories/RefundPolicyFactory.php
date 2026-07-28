<?php

namespace Database\Factories;

use App\Models\RefundPolicy;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Config;

/**
 * @extends Factory<RefundPolicy>
 */
class RefundPolicyFactory extends Factory
{
    protected $model = RefundPolicy::class;

    public function definition(): array
    {
        $title = $this->faker->unique()->sentence(4);

        return [
            'title' => $title,
            'slug' => null,
            'description' => $this->faker->paragraph(),
            'target' => RefundPolicyTarget::VENDOR,
            'language' => Config::get('app.locale'),
            'status' => RefundPolicyStatus::PENDING,
            'shop_id' => Shop::factory()->create()->id,
        ];
    }
}
