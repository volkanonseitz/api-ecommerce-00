<?php

namespace App\DTO;

class WithdrawData
{
    public function __construct(
        public readonly int $shop_id,
        public readonly float $amount,
        public readonly ?string $payment_method,
        public readonly ?string $details,
        public readonly ?string $note,
        public readonly ?string $status,
    ) {}

    public static function fromRequest(array $data, ?string $status = null): self
    {
        return new self(
            shop_id: $data['shop_id'],
            amount: $data['amount'],
            payment_method: $data['payment_method'] ?? null,
            details: $data['details'] ?? null,
            note: $data['note'] ?? null,
            status: $status,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'shop_id' => $this->shop_id,
            'amount' => $this->amount,
            'payment_method' => $this->payment_method,
            'details' => $this->details,
            'note' => $this->note,
            'status' => $this->status,
        ], fn($v) => !is_null($v));
    }
}