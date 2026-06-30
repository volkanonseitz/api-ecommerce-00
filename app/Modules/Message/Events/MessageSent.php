<?php

declare(strict_types=1);

namespace App\Modules\Message\Events;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageSent
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly Message $message,
        public readonly Conversation $conversation,
        public readonly string $type, // 'user' or 'shop'
        public readonly User $sender,
    ) {}
}
