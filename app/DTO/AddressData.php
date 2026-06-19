<?php

namespace App\DTO;

class AddressData
{
    public function __construct(
        public readonly string $title,
        public readonly string $type,
        public readonly bool $default,
        public readonly array $address,
        public readonly ?array $location = null,
    ) {}

    public static function fromRequest(array $data): self
    {
        return new self(
            title: $data['title'],
            type: $data['type'],
            default: $data['default'] ?? false,
            address: $data['address'],
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
            'location' => $this->location,
        ], static fn ($value) => $value !== null);
    }
}
