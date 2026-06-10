<?php

namespace App\DTO;

class RefundPolicyData
{
    public function __construct(
        public readonly ?string $title,
        public readonly ?string $slug,
        public readonly ?string $target,
        public readonly ?string $status,
        public readonly ?string $description,
        public readonly ?int $shop_id,
        public readonly ?string $language,
    ) {}

    public static function fromRequest(array $data): self
    {
        return new self(
            title: $data['title'] ?? null,
            slug: $data['slug'] ?? null,
            target: $data['target'] ?? null,
            status: $data['status'] ?? null,
            description: $data['description'] ?? null,
            shop_id: $data['shop_id'] ?? null,
            language: $data['language'] ?? config('shop.default_language', 'en'),
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'title' => $this->title,
            'slug' => $this->slug,
            'target' => $this->target,
            'status' => $this->status,
            'description' => $this->description,
            'shop_id' => $this->shop_id,
            'language' => $this->language,
        ], fn($v) => !is_null($v));
    }
}