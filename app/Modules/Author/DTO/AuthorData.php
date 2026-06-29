<?php

declare(strict_types=1);

namespace App\Modules\Author\DTO;

final readonly class AuthorData
{
    public function __construct(
        public ?string $name,
        public ?string $slug,
        public ?string $bio,
        public ?int $shop_id,
        public ?array $image,
        public ?array $cover_image,
        public ?bool $is_approved,
        public ?string $language,
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
            language: $data['language'] ?? config('shop.default_language', 'id'),
        );
    }
}
