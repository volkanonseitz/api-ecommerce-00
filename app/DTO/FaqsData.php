<?php

namespace App\DTO;

class FaqsData
{
    public function __construct(
        public readonly ?string $faq_title,
        public readonly ?string $faq_description,
        public readonly ?string $language,
        public readonly ?string $slug,
        public readonly ?int $user_id,
        public readonly ?int $shop_id,
        public readonly ?string $faq_type,
        public readonly ?string $issued_by,
    ) {}

    public static function fromRequest(array $data, ?int $userId = null): self
    {
        return new self(
            faq_title: $data['faq_title'] ?? null,
            faq_description: $data['faq_description'] ?? null,
            language: $data['language'] ?? config('shop.default_language', 'id'),
            slug: $data['slug'] ?? null,
            user_id: $userId,
            shop_id: $data['shop_id'] ?? null,
            faq_type: $data['faq_type'] ?? null,
            issued_by: $data['issued_by'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'faq_title' => $this->faq_title,
            'faq_description' => $this->faq_description,
            'language' => $this->language,
            'slug' => $this->slug,
            'user_id' => $this->user_id,
            'shop_id' => $this->shop_id,
            'faq_type' => $this->faq_type,
            'issued_by' => $this->issued_by,
        ], fn($v) => !is_null($v));
    }
}