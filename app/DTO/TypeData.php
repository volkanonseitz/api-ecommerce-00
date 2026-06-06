<?php

namespace App\DTO;

class TypeData
{
    public function __construct(
        public readonly ?string $name,
        public readonly ?string $slug,
        public readonly ?string $icon,
        public readonly ?array $banners,
        public readonly ?array $settings,
        public readonly ?array $promotional_sliders,
        public readonly ?array $images,
        public readonly ?string $language,
    ) {}

    public static function fromRequest(array $data): self
    {
        return new self(
            name: $data['name'] ?? null,
            slug: $data['slug'] ?? null,
            icon: $data['icon'] ?? null,
            banners: $data['banners'] ?? null,
            settings: $data['settings'] ?? null,
            promotional_sliders: $data['promotional_sliders'] ?? null,
            images: $data['images'] ?? null,
            language: $data['language'] ?? config('shop.default_language', 'en'),
        );
    }
}