<?php

namespace App\Events;

use App\Models\Message;
use App\Models\Conversation;
use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageSent
{
    use Dispatchable, SerializesModels;

    public Message $message;
    public Conversation $conversation;
    public string $type; // 'user' or 'shop'
    public User $sender;

    public function __construct(Message $message, Conversation $conversation, string $type, User $sender)
    {
        $this->message = $message;
        $this->conversation = $conversation;
        $this->type = $type;
        $this->sender = $sender;
    }
}