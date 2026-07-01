<?php

declare(strict_types=1);

namespace App\Modules\Refund\DTO;

use Illuminate\Http\Request;

class RefundData
{
    public function __construct(
        public readonly int $orderId,
        public readonly ?string $title,
        public readonly ?string $description,
        public readonly ?array $images,
        public readonly ?int $refundReasonId,
        public readonly ?int $customerId,
        public readonly ?int $shopId,
        public readonly ?float $amount,
        public readonly ?string $status,
    ) {}

    public static function fromRequest(Request $request, ?int $customerId = null, ?int $shopId = null): self
    {
        return new self(
            orderId: (int) $request->input('order_id'),
            title: $request->input('title'),
            description: $request->input('description'),
            images: $request->input('images'),
            refundReasonId: $request->input('refund_reason_id'),
            customerId: $customerId ?? $request->user()->id,
            shopId: $shopId ?? $request->input('shop_id'),
            amount: $request->input('amount'),
            status: $request->input('status', 'pending'),
        );
    }

    public function toArray(): array
    {
        return [
            'order_id' => $this->orderId,
            'title' => $this->title,
            'description' => $this->description,
            'images' => $this->images,
            'refund_reason_id' => $this->refundReasonId,
            'customer_id' => $this->customerId,
            'shop_id' => $this->shopId,
            'amount' => $this->amount,
            'status' => $this->status,
        ];
    }
}
