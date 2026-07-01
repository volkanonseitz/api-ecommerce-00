<?php

declare(strict_types=1);

namespace App\Modules\Review\DTO;

use Illuminate\Http\Request;

class ReviewData
{
    public function __construct(
        public readonly int $orderId,
        public readonly int $productId,
        public readonly ?int $variationOptionId,
        public readonly int $userId,
        public readonly int $shopId,
        public readonly ?string $comment,
        public readonly int $rating,
        public readonly ?array $photos,
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            orderId: (int) $request->input('order_id'),
            productId: (int) $request->input('product_id'),
            variationOptionId: $request->input('variation_option_id') ? (int) $request->input('variation_option_id') : null,
            userId: (int) $request->user()->id,
            shopId: (int) $request->input('shop_id'),
            comment: $request->input('comment'),
            rating: (int) $request->input('rating'),
            photos: $request->input('photos'),
        );
    }

    public function toArray(): array
    {
        return [
            'order_id' => $this->orderId,
            'product_id' => $this->productId,
            'variation_option_id' => $this->variationOptionId,
            'user_id' => $this->userId,
            'shop_id' => $this->shopId,
            'comment' => $this->comment,
            'rating' => $this->rating,
            'photos' => $this->photos,
        ];
    }
}
