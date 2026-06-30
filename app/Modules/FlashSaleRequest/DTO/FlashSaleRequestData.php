<?php

declare(strict_types=1);

namespace App\Modules\FlashSaleRequest\DTO;

final class FlashSaleRequestData
{
    public function __construct(
        public readonly string $title,
        public readonly ?string $note,
        public readonly int $flash_sale_id,
        public readonly string $language,
        public readonly ?array $requested_product_ids,
        public readonly ?bool $request_status,
    ) {}

    public static function fromRequest(array $data, ?string $language = null): self
    {
        return new self(
            title: $data['title'],
            note: $data['note'] ?? null,
            flash_sale_id: $data['flash_sale_id'],
            language: $language ?? config('shop.default_language', 'id'),
            requested_product_ids: $data['requested_product_ids'] ?? null,
            request_status: $data['request_status'] ?? false,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'title' => $this->title,
            'note' => $this->note,
            'flash_sale_id' => $this->flash_sale_id,
            'language' => $this->language,
            'request_status' => $this->request_status,
        ], fn ($v) => ! is_null($v));
    }
}
