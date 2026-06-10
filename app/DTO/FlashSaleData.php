<?php

namespace App\DTO;

class FlashSaleData
{
    public function __construct(
        public readonly ?string $title,
        public readonly ?string $description,
        public readonly ?string $start_date,
        public readonly ?string $end_date,
        public readonly ?string $language,
        public readonly ?string $slug,
        public readonly ?array $image,
        public readonly ?array $cover_image,
        public readonly ?float $rate,
        public readonly ?string $type,
        public readonly ?string $sale_status,
        public readonly ?array $sale_builder,
    ) {}

    public static function fromRequest(array $data): self
    {
        return new self(
            title: $data['title'] ?? null,
            description: $data['description'] ?? null,
            start_date: $data['start_date'] ?? null,
            end_date: $data['end_date'] ?? null,
            language: $data['language'] ?? config('shop.default_language', 'en'),
            slug: $data['slug'] ?? null,
            image: $data['image'] ?? null,
            cover_image: $data['cover_image'] ?? null,
            rate: $data['rate'] ?? null,
            type: $data['type'] ?? null,
            sale_status: $data['sale_status'] ?? null,
            sale_builder: $data['sale_builder'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'title' => $this->title,
            'description' => $this->description,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'language' => $this->language,
            'slug' => $this->slug,
            'image' => $this->image,
            'cover_image' => $this->cover_image,
            'rate' => $this->rate,
            'type' => $this->type,
            'sale_status' => $this->sale_status,
            'sale_builder' => $this->sale_builder,
        ], fn($v) => !is_null($v));
    }
}