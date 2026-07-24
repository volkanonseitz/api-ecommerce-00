<?php

declare(strict_types=1);

namespace App\Modules\Review\Http\Controllers;

use App\Http\Controllers\BaseController;
use App\Models\Review;
use App\Modules\Review\DTO\ReviewData;
use App\Modules\Review\Http\Requests\ReviewCreateRequest;
use App\Modules\Review\Http\Requests\ReviewUpdateRequest;
use App\Modules\Review\Http\Resources\ReviewResource;
use App\Modules\Review\Services\ReviewService;
use App\Modules\Settings\Services\SettingsService; // Use modular SettingsService
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ReviewController extends BaseController
{
    public function __construct(
        private ReviewService $reviewService,
        private SettingsService $settingsService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $limit = (int) $request->get('limit', 15);
        $reviews = $this->reviewService->getReviews($request, $request->user())->paginate($limit);

        return $this->sendPaginated(
            $reviews,
            ReviewResource::collection($reviews->getCollection()),
            'Reviews retrieved successfully'
        );
    }

    public function store(ReviewCreateRequest $request): JsonResponse
    {
        $this->authorize('create', Review::class);

        $settings = $this->settingsService->getSettings(config('shop.default_language', 'id'));
        $settingsOptions = $settings?->options ?? [];

        $productId = (int) $request->input('product_id');
        $orderId = (int) $request->input('order_id');
        $userId = $request->user()->id;
        $shopId = (int) $request->input('shop_id');
        $variationOptionId = $request->input('variation_option_id');

        if (! $this->reviewService->validateProductInOrder($orderId, $productId)) {
            throw new HttpException(404, 'Product not found in the given order.');
        }

        $reviewSystem = $settingsOptions['reviewSystem']['value'] ?? null;
        if ($reviewSystem === 'review_single_time') {
            $exists = $this->reviewService->reviewExistsForOrder(
                $userId, $orderId, $productId, $shopId, (int) $variationOptionId
            );
            if ($exists) {
                throw new HttpException(400, 'You have already reviewed this product for this order.');
            }
        }

        $data = ReviewData::fromRequest($request);
        $review = $this->reviewService->createReview($data, $request->user());

        return $this->sendSuccess(
            new ReviewResource($review),
            'Review created successfully',
            201
        );
    }

    public function show(int $id): JsonResponse
    {
        $review = Review::findOrFail($id);

        return $this->sendSuccess(
            new ReviewResource($review),
            'Review retrieved successfully'
        );
    }

    public function update(ReviewUpdateRequest $request, int $id): JsonResponse
    {
        $review = Review::findOrFail($id);
        $this->authorize('update', $review);

        $data = ReviewData::fromRequest($request);
        $updated = $this->reviewService->updateReview($review, $data, $request->user());

        return $this->sendSuccess(
            new ReviewResource($updated),
            'Review updated successfully'
        );
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $review = Review::findOrFail($id);
        $this->authorize('delete', $review);

        $this->reviewService->deleteReview($review, $request->user());

        return $this->sendSuccess(
            null,
            'Review deleted successfully'
        );
    }
}
