<?php

namespace App\DTO;

class AuthorData
{
    public function __construct(
        public readonly ?string $name,
        public readonly ?string $slug,
        public readonly ?string $bio,
        public readonly ?int $shop_id,
        public readonly ?array $image,
        public readonly ?array $cover_image,
        public readonly ?bool $is_approved,
        public readonly ?string $language,
    ) {}

    public static function fromRequest(array $data): self
    {
        return new self(
            name: $data['name'] ?? null,
            slug: $data['slug'] ?? null,
            bio: $data['bio'] ?? null,
            shop_id: $data['shop_id'] ?? null,
            image: $data['image'] ?? null,
            cover_image: $data['cover_image'] ?? null,
            is_approved: $data['is_approved'] ?? null,
            language: $data['language'] ?? config('shop.default_language', 'en'),
        );
    }
}