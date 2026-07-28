<?php

namespace Database\Factories;

use App\Models\NotifyLogs;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<NotifyLogs>
 */
class NotifyLogsFactory extends Factory
{
    protected $model = NotifyLogs::class;

    public function definition(): array
    {
        return [
            'receiver' => User::factory(),
            'sender' => User::factory(),
            'notify_type' => $this->faker->randomElement(['email', 'sms']),
            'notify_receiver_type' => 'user',
            'is_read' => $this->faker->boolean(),
            'notify_tracker' => Str::random(16),
            'notify_text' => $this->faker->sentence(),
        ];
    }
}
