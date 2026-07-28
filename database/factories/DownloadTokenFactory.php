<?php

namespace Database\Factories;

use App\Models\DigitalFile;
use App\Models\DownloadToken;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class DownloadTokenFactory extends Factory
{
    protected $model = DownloadToken::class;

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

        return [
            'token' => hash('sha256', Str::random(60)),
            'digital_file_id' => $digitalFile->id,
            'payload' => json_encode([
                'ip' => $this->faker->ipv4(),
            ]),
            'user_id' => User::factory(),
        ];
    }
}
