<?php

namespace App\DTO;

class OrderData
{
    public function __construct(
        public ?string $tracking_number = null,
        public ?int $customer_id = null,
        public ?int $shop_id = null,
        public ?string $language = null,
        public ?string $order_status = null,
        public ?string $payment_status = null,
        public ?float $amount = null,
        public ?float $sales_tax = 0,
        public ?float $paid_total = null,
        public ?float $total = null,
        public ?string $delivery_time = null,
        public ?string $payment_gateway = null,
        public ?string $altered_payment_gateway = null,
        public ?float $discount = 0,
        public ?int $coupon_id = null,
        public ?string $logistics_provider = null,
        public ?array $billing_address = null,
        public ?array $shipping_address = null,
        public ?float $delivery_fee = 0,
        public ?string $customer_contact = null,
        public ?string $customer_name = null,
        public ?string $note = null,
        public ?int $parent_id = null,
        public ?array $products = null,
        public ?bool $use_wallet_points = false,
        public ?bool $isFullWalletPayment = false,
    ) {}

    public static function fromRequest(array $data): self
    {
        return new self(
            tracking_number: $data['tracking_number'] ?? null,
            customer_id: $data['customer_id'] ?? null,
            shop_id: $data['shop_id'] ?? null,
            language: $data['language'] ?? config('shop.default_language', 'id'),
            order_status: $data['order_status'] ?? null,
            payment_status: $data['payment_status'] ?? null,
            amount: $data['amount'] ?? null,
            sales_tax: $data['sales_tax'] ?? 0,
            paid_total: $data['paid_total'] ?? null,
            total: $data['total'] ?? null,
            delivery_time: $data['delivery_time'] ?? null,
            payment_gateway: $data['payment_gateway'] ?? null,
            altered_payment_gateway: $data['altered_payment_gateway'] ?? null,
            discount: $data['discount'] ?? 0,
            coupon_id: $data['coupon_id'] ?? null,
            logistics_provider: $data['logistics_provider'] ?? null,
            billing_address: $data['billing_address'] ?? null,
            shipping_address: $data['shipping_address'] ?? null,
            delivery_fee: $data['delivery_fee'] ?? 0,
            customer_contact: $data['customer_contact'] ?? null,
            customer_name: $data['customer_name'] ?? null,
            note: $data['note'] ?? null,
            parent_id: $data['parent_id'] ?? null,
            products: $data['products'] ?? null,
            use_wallet_points: $data['use_wallet_points'] ?? false,
            isFullWalletPayment: $data['isFullWalletPayment'] ?? false,
        );
    }
}