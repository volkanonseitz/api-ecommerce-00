<?php

declare(strict_types=1);

namespace App\Modules\PaymentIntent\DTO;

final class PaymentIntentData
{
    public function __construct(
        public readonly string $tracking_number,
        public readonly string $payment_gateway,
        public readonly ?bool $recall_gateway = false,
    ) {}

    public static function fromRequest(array $data): self
    {
        return new self(
            tracking_number: $data['tracking_number'],
            payment_gateway: $data['payment_gateway'],
            recall_gateway: $data['recall_gateway'] ?? false,
        );
    }
}
