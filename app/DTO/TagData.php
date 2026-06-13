<?php

namespace App\DTO;

class TagData
{
    public function __construct(
        public readonly ?string $name,
        public readonly ?string $slug,
        public readonly ?int $type_id,
        public readonly ?string $icon,
        public readonly ?array $image,
        public readonly ?string $details,
        public readonly ?string $language,
    ) {}

    public static function fromRequest(array $data): self
    {
        return new self(
            name: $data['name'] ?? null,
            slug: $data['slug'] ?? null,
            type_id: $data['type_id'] ?? null,
            icon: $data['icon'] ?? null,
            image: $data['image'] ?? null,
            details: $data['details'] ?? null,
            language: $data['language'] ?? config('shop.default_language', 'id'),
        );
    }
}