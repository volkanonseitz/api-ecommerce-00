<?php

namespace App\DTO;

class PaymentMethodData
{
    public function __construct(
        public readonly string $method_key,
        public readonly bool $default_card,
        public readonly string $payment_gateway,
    ) {}

    public static function fromRequest(array $data): self
    {
        return new self(
            method_key: $data['method_key'],
            default_card: $data['default_card'] ?? false,
            payment_gateway: $data['payment_gateway'],
        );
    }
}