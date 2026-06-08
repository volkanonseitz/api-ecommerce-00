<?php

namespace App\DTO;

class TermsData
{
    public function __construct(
        public readonly ?string $title,
        public readonly ?string $description,
        public readonly ?string $language,
        public readonly ?string $slug,
        public readonly ?int $shop_id,
        public readonly ?int $user_id,
    ) {}

    public static function fromRequest(array $data, ?int $userId = null): self
    {
        return new self(
            title: $data['title'] ?? null,
            description: $data['description'] ?? null,
            language: $data['language'] ?? config('shop.default_language', 'en'),
            slug: $data['slug'] ?? null,
            shop_id: $data['shop_id'] ?? null,
            user_id: $userId,
        );
    }
}