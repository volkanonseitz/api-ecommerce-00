<?php

namespace App\DTO;

use Carbon\Carbon;

class CouponData
{
    public function __construct(
        public readonly ?string $code,
        public readonly ?string $language,
        public readonly ?string $description,
        public readonly ?array $image,
        public readonly ?string $type,
        public readonly ?float $amount,
        public readonly ?float $minimum_cart_amount,
        public readonly ?string $active_from,
        public readonly ?string $expire_at,
        public readonly ?string $target,
        public readonly ?bool $is_approve,
        public readonly ?int $user_id,
        public readonly ?int $shop_id,
    ) {}

    public static function fromRequest(array $data, ?int $userId = null): self
    {
        return new self(
            code: $data['code'] ?? null,
            language: $data['language'] ?? config('shop.default_language', 'en'),
            description: $data['description'] ?? null,
            image: $data['image'] ?? null,
            type: $data['type'] ?? null,
            amount: $data['amount'] ?? null,
            minimum_cart_amount: $data['minimum_cart_amount'] ?? 0,
            active_from: $data['active_from'] ?? null,
            expire_at: $data['expire_at'] ?? null,
            target: $data['target'] ?? null,
            is_approve: $data['is_approve'] ?? null,
            user_id: $userId,
            shop_id: $data['shop_id'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'code' => $this->code,
            'language' => $this->language,
            'description' => $this->description,
            'image' => $this->image,
            'type' => $this->type,
            'amount' => $this->amount,
            'minimum_cart_amount' => $this->minimum_cart_amount,
            'active_from' => $this->active_from,
            'expire_at' => $this->expire_at,
            'target' => $this->target,
            'is_approve' => $this->is_approve,
            'user_id' => $this->user_id,
            'shop_id' => $this->shop_id,
        ], fn($v) => !is_null($v));
    }
}