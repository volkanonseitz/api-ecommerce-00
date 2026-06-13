<?php

namespace App\DTO;

class RefundReasonData
{
    public function __construct(
        public readonly ?string $name,
        public readonly ?string $slug,
        public readonly ?string $language,
    ) {}

    public static function fromRequest(array $data): self
    {
        return new self(
            name: $data['name'] ?? null,
            slug: $data['slug'] ?? null,
            language: $data['language'] ?? config('shop.default_language', 'id'),
        );
    }
}