<?php

namespace App\DTO;

class AddressData
{
    public function __construct(
        public readonly string $title,
        public readonly string $type,
        public readonly bool $default,
        public readonly array $address,
        public readonly int $customer_id,
        public readonly ?array $location = null,
    ) {}

    public static function fromRequest(array $data): self
    {
        return new self(
            title: $data['title'],
            type: $data['type'],
            default: $data['default'] ?? false,
            address: $data['address'],
            customer_id: $data['customer_id'],
            location: $data['location'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'title' => $this->title,
            'type' => $this->type,
            'default' => $this->default,
            'address' => $this->address,
            'customer_id' => $this->customer_id,
            'location' => $this->location,
        ], fn($v) => !is_null($v));
    }
}