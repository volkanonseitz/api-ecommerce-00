<?php

namespace App\DTO;

class ResourceData
{
    public function __construct(
        public readonly ?string $name,
        public readonly ?string $slug,
        public readonly ?string $type,
        public readonly ?float $price,
        public readonly ?array $image,
        public readonly ?string $icon,
        public readonly ?string $details,
        public readonly ?string $language,
        public readonly ?bool $is_approved,
    ) {}

    public static function fromRequest(array $data): self
    {
        return new self(
            name: $data['name'] ?? null,
            slug: $data['slug'] ?? null,
            type: $data['type'] ?? null,
            price: $data['price'] ?? null,
            image: $data['image'] ?? null,
            icon: $data['icon'] ?? null,
            details: $data['details'] ?? null,
            language: $data['language'] ?? config('shop.default_language', 'en'),
            is_approved: $data['is_approved'] ?? null,
        );
    }
}