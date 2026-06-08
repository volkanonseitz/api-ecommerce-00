<?php

namespace App\DTO;

class ReviewData
{
    public function __construct(
        public readonly ?int $order_id,
        public readonly ?int $product_id,
        public readonly ?int $variation_option_id,
        public readonly ?int $user_id,
        public readonly ?int $shop_id,
        public readonly ?string $comment,
        public readonly ?int $rating,
        public readonly ?array $photos,
    ) {}

    public static function fromRequest(array $data, ?int $userId = null): self
    {
        return new self(
            order_id: $data['order_id'] ?? null,
            product_id: $data['product_id'] ?? null,
            variation_option_id: $data['variation_option_id'] ?? null,
            user_id: $userId,
            shop_id: $data['shop_id'] ?? null,
            comment: $data['comment'] ?? null,
            rating: $data['rating'] ?? null,
            photos: $data['photos'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'order_id' => $this->order_id,
            'product_id' => $this->product_id,
            'variation_option_id' => $this->variation_option_id,
            'user_id' => $this->user_id,
            'shop_id' => $this->shop_id,
            'comment' => $this->comment,
            'rating' => $this->rating,
            'photos' => $this->photos,
        ], fn($v) => !is_null($v));
    }
}