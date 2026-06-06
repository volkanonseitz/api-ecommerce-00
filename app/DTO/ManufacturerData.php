<?php

namespace App\DTO;

class ManufacturerData
{
    public function __construct(
        public readonly ?string $name,
        public readonly ?string $slug,
        public readonly ?string $description,
        public readonly ?int $type_id,
        public readonly ?int $shop_id,
        public readonly ?array $image,
        public readonly ?array $cover_image,
        public readonly ?bool $is_approved,
        public readonly ?string $language,
        public readonly ?string $website,
        public readonly ?array $socials,
    ) {}

    public static function fromRequest(array $data): self
    {
        return new self(
            name: $data['name'] ?? null,
            slug: $data['slug'] ?? null,
            description: $data['description'] ?? null,
            type_id: $data['type_id'] ?? null,
            shop_id: $data['shop_id'] ?? null,
            image: $data['image'] ?? null,
            cover_image: $data['cover_image'] ?? null,
            is_approved: $data['is_approved'] ?? null,
            language: $data['language'] ?? config('shop.default_language', 'id'),
            website: $data['website'] ?? null,
            socials: $data['socials'] ?? null,
        );
    }
}