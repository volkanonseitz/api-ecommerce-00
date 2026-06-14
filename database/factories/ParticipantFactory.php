<?php

namespace Database\Factories;

use App\Models\Participant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Participant>
 */
class ParticipantFactory extends Factory
{
    protected $model = Participant::class;

    public function definition(): array
    {
        $conversation = Conversation::factory()->create();
        $user = User::factory()->create();
        $shop = Shop::factory()->create();
        $message = Message::factory()->create(['conversation_id' => $conversation->id]);

        return [
            'conversation_id' => $conversation->id,
            'type' => $this->faker->randomElement(['shop', 'user']),
            'user_id' => $user->id,
            'shop_id' => $shop->id,
            'message_id' => $message->id,
            'notify' => $this->faker->boolean(),
            'last_read' => $this->faker->optional()->dateTime(),
        ];
    }
}
