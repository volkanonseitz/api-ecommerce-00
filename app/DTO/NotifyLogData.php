<?php

namespace App\DTO;

class NotifyLogData
{
    public function __construct(
        public readonly int $receiver,
        public readonly ?int $sender,
        public readonly string $notify_type,
        public readonly string $notify_receiver_type,
        public readonly bool $is_read,
        public readonly string $notify_text,
        public readonly string $notify_tracker,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            receiver: $data['receiver'],
            sender: $data['sender'] ?? null,
            notify_type: $data['notify_type'],
            notify_receiver_type: $data['notify_receiver_type'],
            is_read: $data['is_read'] ?? false,
            notify_text: $data['notify_text'],
            notify_tracker: $data['notify_tracker'],
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'receiver' => $this->receiver,
            'sender' => $this->sender,
            'notify_type' => $this->notify_type,
            'notify_receiver_type' => $this->notify_receiver_type,
            'is_read' => $this->is_read,
            'notify_text' => $this->notify_text,
            'notify_tracker' => $this->notify_tracker,
        ], fn($v) => !is_null($v));
    }
}