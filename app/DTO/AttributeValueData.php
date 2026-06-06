<?php

namespace App\DTO;

class AttributeValueData
{
    public function __construct(
        public readonly ?string $value,
        public readonly ?string $meta,
        public readonly ?float $price,
        public readonly ?int $shop_id,
        public readonly ?int $attribute_id,
        public readonly ?string $language,
    ) {}

    public static function fromRequest(array $data): self
    {
        return new self(
            value: $data['value'] ?? null,
            meta: $data['meta'] ?? null,
            price: $data['price'] ?? null,
            shop_id: $data['shop_id'] ?? null,
            attribute_id: $data['attribute_id'] ?? null,
            language: $data['language'] ?? config('shop.default_language', 'en'),
        );
    }
}