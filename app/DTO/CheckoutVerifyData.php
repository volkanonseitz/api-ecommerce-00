<?php

namespace App\DTO;

class CheckoutVerifyData
{
    public function __construct(
        public readonly float $amount,
        public readonly ?int $customer_id,
        public readonly array $products,
        public readonly ?array $billing_address,
        public readonly ?array $shipping_address,
    ) {}

    public static function fromRequest(array $data): self
    {
        return new self(
            amount: $data['amount'],
            customer_id: $data['customer_id'] ?? null,
            products: $data['products'],
            billing_address: $data['billing_address'] ?? null,
            shipping_address: $data['shipping_address'] ?? null,
        );
    }
}