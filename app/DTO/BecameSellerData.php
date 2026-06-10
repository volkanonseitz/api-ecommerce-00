<?php

namespace App\DTO;

class BecameSellerData
{
    public function __construct(
        public readonly array $page_options,
        public readonly ?string $language,
    ) {}

    public static function fromRequest(array $data): self
    {
        return new self(
            page_options: $data['page_options'] ?? [],
            language: $data['language'] ?? config('shop.default_language', 'en'),
        );
    }
}