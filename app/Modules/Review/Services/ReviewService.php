<?php

declare(strict_types=1);

namespace App\Modules\Review\Services;

use App\Events\ReviewCreated;
use App\Models\Order;
use App\Models\Review;
use App\Models\User;
use App\Modules\Review\DTO\ReviewData;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class ReviewService
{
    public function getReviews(Request $request, ?User $user = null): Builder
    {
        $query = Review::query()->with(['user', 'product', 'order', 'shop']);
        
        if ($request->has('product_id')) {
            $query->where('product_id', (int) $request->input('product_id'));
        }
        
        if ($request->has('shop_id')) {
            $query->where('shop_id', (int) $request->input('shop_id'));
        }

        if ($request->has('user_id')) {
            $query->where('user_id', (int) $request->input('user_id'));
        }

        return $query;
    }

    public function validateProductInOrder(int $orderId, int $productId): bool
    {
        return Order::where('id', $orderId)
            ->whereHas('products', fn ($q) => $q->where('product_id', $productId))
            ->exists();
    }

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

    public function createReview(ReviewData $data, User $user): Review
    {
        $review = Review::create($data->toArray());
        event(new ReviewCreated($review));

        return $review;
    }

    public function updateReview(Review $review, ReviewData $data, User $user): Review
    {
        $review->update($data->toArray());
        return $review->fresh();
    }

    public function deleteReview(Review $review, User $user): void
    {
        $review->delete();
    }
}
