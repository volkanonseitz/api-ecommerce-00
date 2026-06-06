<?php

namespace App\DTO;

class VariationOptionData
{
    public function __construct(
        public readonly ?int $id,
        public readonly ?string $sku,
        public readonly ?float $price,
        public readonly ?float $sale_price,
        public readonly ?int $quantity,
        public readonly ?array $options,
        public readonly ?bool $is_digital,
        public readonly ?array $digital_file,
        public readonly ?bool $inform_purchased_customer,
        public readonly ?string $product_update_message,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'] ?? null,
            sku: $data['sku'] ?? null,
            price: $data['price'] ?? null,
            sale_price: $data['sale_price'] ?? null,
            quantity: $data['quantity'] ?? null,
            options: $data['options'] ?? null,
            is_digital: $data['is_digital'] ?? false,
            digital_file: $data['digital_file'] ?? null,
            inform_purchased_customer: $data['inform_purchased_customer'] ?? false,
            product_update_message: $data['product_update_message'] ?? null,
        );
    }
}