<?php

namespace Database\Factories;

use App\Models\Faqs;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Faqs>
 */
class FaqsFactory extends Factory
{
    protected $model = Faqs::class;

    public function definition(): array
    {
        $title = $this->faker->sentence();
        return [
            'user_id' => User::factory(),
            'shop_id' => null,
            'faq_title' => $title,
            'slug' => null,
            'faq_description' => $this->faker->paragraph(),
            'faq_type' => $this->faker->randomElement(['general', 'product']),
            'issued_by' => $this->faker->name(),
            'language' => Config::get('app.locale'),
        ];
    }
}
