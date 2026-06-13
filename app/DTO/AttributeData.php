<?php

namespace App\DTO;

class AttributeData
{
    public function __construct(
        public readonly ?string $name,
        public readonly ?string $slug,
        public readonly ?int $shop_id,
        public readonly ?string $language,
        public readonly ?array $values, // array of value data (with id for update)
    ) {}

    public static function fromRequest(array $data): self
    {
        return new self(
            name: $data['name'] ?? null,
            slug: $data['slug'] ?? null,
            shop_id: $data['shop_id'] ?? null,
            language: $data['language'] ?? config('shop.default_language', 'id'),
            values: $data['values'] ?? null,
        );
    }
}