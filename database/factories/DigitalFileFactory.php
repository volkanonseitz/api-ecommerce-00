<?php

namespace Database\Factories;

use App\Models\DigitalFile;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DigitalFile>
 */
class DigitalFileFactory extends Factory
{
    protected $model = DigitalFile::class;

    public function definition(): array
    {
        return [
            'attachment_id' => 1, // asumsi attachment id
            'url' => $this->faker->url(),
            'file_name' => $this->faker->word() . '.pdf',
            'fileable_type' => 'App\Models\Product',
            'fileable_id' => Product::factory(),
        ];
    }
}
