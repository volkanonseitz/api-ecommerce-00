<?php

namespace Database\Factories;

use App\Models\DownloadToken;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DownloadToken>
 */
class DownloadTokenFactory extends Factory
{
    protected $model = DownloadToken::class;

    public function definition(): array
    {
        return [
            'token' => hash('sha256', Str::random(60)),
            'digital_file_id' => DigitalFile::factory(),
            'payload' => json_encode(['ip' => $this->faker->ipv4()]),
            'user_id' => User::factory(),
        ];
    }
}
