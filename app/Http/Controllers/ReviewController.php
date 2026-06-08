<?php

namespace App\Http\Controllers;

use App\Services\ReviewService;
use App\Services\SettingsService;
use App\Http\Requests\ReviewCreateRequest;
use App\Http\Requests\ReviewUpdateRequest;
use App\Http\Resources\ReviewResource;
use App\DTO\ReviewData;
use App\Models\Review;
use App\Models\Settings;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ReviewController extends Controller
{
    public function __construct(
        private ReviewService $reviewService,
        private SettingsService $settingsService
    ) {}

    /**
     * GET /reviews
     */
    public function index(Request $request)
    {
        $limit = $request->limit ?? 15;
        $reviews = $this->reviewService->getReviews($request)->paginate($limit);
        return ReviewResource::collection($reviews);
    }

    /**
     * POST /reviews
     */
    public function store(ReviewCreateRequest $request)
    {
        $settings = $this->settingsService->getFirst();

        $productId = $request->product_id;
        $orderId = $request->order_id;
        $userId = $request->user()->id;
        $shopId = $request->shop_id;
        $variationOptionId = $request->variation_option_id ?? null;

        // Validasi: apakah produk ada dalam order?
        if (!$this->reviewService->validateProductInOrder($orderId, $productId)) {
            throw new ModelNotFoundException(config('notice.NOT_FOUND'));
        }

        // Jika sistem review adalah "review_single_time"
        $reviewSystem = $settings->options['reviewSystem']['value'] ?? null;
        if ($reviewSystem === 'review_single_time') {
            $exists = $this->reviewService->reviewExistsForOrder(
                $userId, $orderId, $productId, $shopId, $variationOptionId
            );
            if ($exists) {
                throw new HttpException(400, config('notice.ALREADY_GIVEN_REVIEW_FOR_THIS_PRODUCT'));
            }
        }

        $data = ReviewData::fromRequest($request->validated(), $userId);
        $review = $this->reviewService->createReview($data);
        return new ReviewResource($review);
    }

    /**
     * GET /reviews/{id}
     */
    public function show($id)
    {
        $review = $this->reviewService->findOrFail($id);
        return new ReviewResource($review);
    }

    /**
     * PUT /reviews/{id}
     */
    public function update(ReviewUpdateRequest $request, $id)
    {
        $review = $this->reviewService->findOrFail($id);
        // Cek permission: hanya pembuat review yang bisa update (atau admin? Sesuai asumsi)
        if ($request->user()->id !== $review->user_id && !$request->user()->hasPermissionTo('super_admin')) {
            abort(403, config('notice.NOT_AUTHORIZED'));
        }

        $data = ReviewData::fromRequest($request->validated(), $review->user_id);
        $updated = $this->reviewService->updateReview($review, $data);
        return new ReviewResource($updated);
    }

    /**
     * DELETE /reviews/{id}
     */
    public function destroy(Request $request, $id)
    {
        $review = $this->reviewService->findOrFail($id);
        // Cek permission: hanya pembuat review atau super admin yang bisa hapus
        if ($request->user()->id !== $review->user_id && !$request->user()->hasPermissionTo('super_admin')) {
            abort(403, config('notice.NOT_AUTHORIZED'));
        }
        $this->reviewService->deleteReview($review);
        return response()->json(['message' => 'Review deleted successfully']);
    }
}