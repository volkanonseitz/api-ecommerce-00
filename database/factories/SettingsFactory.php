<?php

namespace Database\Factories;

use App\Models\Settings;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Settings>
 */
class SettingsFactory extends Factory
{
    protected $model = Settings::class;

    public function definition(): array
    {
        return [
            'options' => json_encode([
                'site_name' => $this->faker->company(),
                'currency' => 'USD',
            ]),
            'language' => Config::get('app.locale'),
        ];
    }
}
