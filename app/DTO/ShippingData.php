<?php

namespace App\DTO;

class ShippingData
{
    public function __construct(
        public readonly string $name,
        public readonly string $type,
        public readonly ?float $amount,
        public readonly ?bool $is_global,
    ) {}

    public static function fromRequest(array $data): self
    {
        return new self(
            name: $data['name'],
            type: $data['type'],
            amount: $data['amount'] ?? null,
            is_global: $data['is_global'] ?? false,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'name' => $this->name,
            'type' => $this->type,
            'amount' => $this->amount,
            'is_global' => $this->is_global,
        ], fn($v) => !is_null($v));
    }
}