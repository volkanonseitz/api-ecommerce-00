<?php

namespace App\DTO;

class OwnershipTransferData
{
    public function __construct(
        public readonly int $shop_id,
        public readonly int $from,
        public readonly int $to,
        public readonly ?string $message,
        public readonly ?int $created_by,
        public readonly ?string $status,
    ) {}

    public static function fromRequest(array $data, int $fromUserId): self
    {
        return new self(
            shop_id: $data['shop_id'],
            from: $fromUserId,
            to: $data['vendor_id'],
            message: $data['message'] ?? null,
            created_by: $fromUserId,
            status: 'pending',
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'shop_id' => $this->shop_id,
            'from' => $this->from,
            'to' => $this->to,
            'message' => $this->message,
            'created_by' => $this->created_by,
            'status' => $this->status,
        ], fn($v) => !is_null($v));
    }
}