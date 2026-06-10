<?php

namespace App\DTO;

class WishlistData
{
    public function __construct(
        public readonly int $product_id,
        public readonly int $user_id,
        public readonly ?int $variation_option_id,
    ) {}

    public static function fromRequest(array $data, int $userId): self
    {
        return new self(
            product_id: $data['product_id'],
            user_id: $userId,
            variation_option_id: $data['variation_option_id'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'product_id' => $this->product_id,
            'user_id' => $this->user_id,
            'variation_option_id' => $this->variation_option_id,
        ], fn($v) => !is_null($v));
    }
}