<?php

namespace App\Services;

use App\Models\Review;
use App\Models\Order;
use App\Models\Settings;
use App\DTO\ReviewData;
use App\Actions\CreateReviewAction;
use App\Actions\UpdateReviewAction;
use App\Events\ReviewCreated;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class ReviewService
{
    public function __construct(
        private CreateReviewAction $createReview,
        private UpdateReviewAction $updateReview,
    ) {}

    public function getReviews(Request $request): Builder
    {
        $query = Review::query();
        if ($request->has('product_id') && !empty($request->product_id)) {
            $query->where('product_id', $request->product_id);
        }
        return $query;
    }

    public function findOrFail(int $id): Review
    {
        return Review::findOrFail($id);
    }

    /**
     * Validasi apakah user sudah membeli produk tersebut di order tertentu
     */
    public function validateProductInOrder(int $orderId, int $productId): bool
    {
        return Order::where('id', $orderId)
            ->whereHas('products', fn($q) => $q->where('product_id', $productId))
            ->exists();
    }

    /**
     * Cek apakah review sudah pernah diberikan (untuk sistem one-time)
     */
    public function reviewExistsForOrder(int $userId, int $orderId, int $productId, ?int $shopId, ?int $variationOptionId = null): bool
    {
        $query = Review::where('user_id', $userId)
            ->where('order_id', $orderId)
            ->where('product_id', $productId)
            ->where('shop_id', $shopId);

        if ($variationOptionId) {
            $query->where('variation_option_id', $variationOptionId);
        }

        return $query->exists();
    }

    public function createReview(ReviewData $data): Review
    {
        $review = $this->createReview->execute($data);
        event(new ReviewCreated($review));
        return $review;
    }

    public function updateReview(Review $review, ReviewData $data): Review
    {
        return $this->updateReview->execute($review, $data);
    }

    public function deleteReview(Review $review): void
    {
        $review->delete();
    }
}