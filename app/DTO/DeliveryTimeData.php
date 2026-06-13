<?php

namespace App\DTO;

class DeliveryTimeData
{
    public function __construct(
        public readonly ?string $title,
        public readonly ?string $slug,
        public readonly ?string $language,
        public readonly ?string $description,
        public readonly ?string $icon,
    ) {}

    public static function fromRequest(array $data): self
    {
        return new self(
            title: $data['title'] ?? null,
            slug: $data['slug'] ?? null,
            language: $data['language'] ?? config('shop.default_language', 'id'),
            description: $data['description'] ?? null,
            icon: $data['icon'] ?? null,
        );
    }
}