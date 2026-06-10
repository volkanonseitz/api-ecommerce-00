<?php

namespace App\DTO;

class MessageData
{
    public function __construct(
        public readonly string $body,
        public readonly int $conversation_id,
        public readonly int $user_id,
    ) {}

    public static function fromRequest(array $data, int $conversationId, int $userId): self
    {
        return new self(
            body: $data['message'],
            conversation_id: $conversationId,
            user_id: $userId,
        );
    }

    public function toArray(): array
    {
        return [
            'body' => $this->body,
            'conversation_id' => $this->conversation_id,
            'user_id' => $this->user_id,
        ];
    }
}