<?php

namespace App\DTO;

class RefundData
{
    public function __construct(
        public readonly int $order_id,
        public readonly ?string $title,
        public readonly ?string $description,
        public readonly ?array $images,
        public readonly ?int $refund_reason_id,
        public readonly ?int $customer_id,
        public readonly ?int $shop_id,
        public readonly ?float $amount,
        public readonly ?string $status,
    ) {}

    public static function fromRequest(array $data, ?int $customerId = null, ?int $shopId = null): self
    {
        return new self(
            order_id: $data['order_id'],
            title: $data['title'] ?? null,
            description: $data['description'] ?? null,
            images: $data['images'] ?? null,
            refund_reason_id: $data['refund_reason_id'] ?? null,
            customer_id: $customerId,
            shop_id: $shopId,
            amount: $data['amount'] ?? null,
            status: $data['status'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'order_id' => $this->order_id,
            'title' => $this->title,
            'description' => $this->description,
            'images' => $this->images,
            'refund_reason_id' => $this->refund_reason_id,
            'customer_id' => $this->customer_id,
            'shop_id' => $this->shop_id,
            'amount' => $this->amount,
            'status' => $this->status,
        ], fn($v) => !is_null($v));
    }
}