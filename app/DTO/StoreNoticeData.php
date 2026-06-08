<?php

namespace App\DTO;

class StoreNoticeData
{
    public function __construct(
        public readonly ?string $priority,
        public readonly ?string $notice,
        public readonly ?string $description,
        public readonly ?string $effective_from,
        public readonly ?string $expired_at,
        public readonly ?string $type,
        public readonly ?array $received_by,
    ) {}

    public static function fromRequest(array $data): self
    {
        return new self(
            priority: $data['priority'] ?? null,
            notice: $data['notice'] ?? null,
            description: $data['description'] ?? null,
            effective_from: $data['effective_from'] ?? null,
            expired_at: $data['expired_at'] ?? null,
            type: $data['type'] ?? null,
            received_by: $data['received_by'] ?? null,
        );
    }
}