<?php

declare(strict_types=1);

namespace App\Modules\Analytics\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Type;
use App\Modules\Analytics\Http\Requests\AnalyticsRequest;
use App\Modules\Analytics\Http\Resources\AnalyticsResource;
use App\Modules\Analytics\Http\Resources\CategoryWiseResource;
use App\Modules\Analytics\Http\Resources\LowStockProductResource;
use App\Modules\Analytics\Http\Resources\TopRatedProductResource;
use App\Modules\Analytics\Services\AnalyticsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AnalyticsController extends Controller
{
    public function __construct(
        private readonly AnalyticsService $analyticsService,
    ) {}

    /**
     * GET /analytics – Ringkasan dashboard.
     */
    public function analytics(Request $request): JsonResponse
    {
        $this->authorize('view-analytics');

        $user = $request->user();
        $data = $this->analyticsService->getAnalytics($user); // cache TTL default 300

        return response()->json([
            'success' => true,
            'data' => new AnalyticsResource($data),
        ]);
    }

    /**
     * GET /low-stock-products
     */
    public function lowStockProducts(AnalyticsRequest $request): JsonResponse
    {
        $this->authorize('view-analytics');

        $user = $request->user();
        $language = $request->input('language', config('shop.default_language', 'id'));
        $typeId = $this->resolveTypeId($request);
        $shopId = $request->input('shop_id');
        $limit = (int) $request->input('limit', 10);

        $products = $this->analyticsService->getLowStockProducts(
            $user,
            $language,
            $typeId,
            $shopId,
            $limit
        );

        return response()->json([
            'success' => true,
            'data' => LowStockProductResource::collection($products),
        ]);
    }

    /**
     * GET /category-wise-product
     */
    public function categoryWiseProduct(AnalyticsRequest $request): JsonResponse
    {
        $this->authorize('view-analytics');

        $user = $request->user();
        $language = $request->input('language', config('shop.default_language', 'id'));
        $limit = (int) $request->input('limit', 15);

        $data = $this->analyticsService->categoryWiseProductCount($user, $language, $limit);

        return response()->json([
            'success' => true,
            'data' => CategoryWiseResource::collection($data),
        ]);
    }

    /**
     * GET /category-wise-product-sale
     */
    public function categoryWiseProductSale(AnalyticsRequest $request): JsonResponse
    {
        $this->authorize('view-analytics');

        $user = $request->user();
        $language = $request->input('language', config('shop.default_language', 'id'));
        $limit = (int) $request->input('limit', 15);

        $data = $this->analyticsService->categoryWiseProductSales($user, $language, $limit);

        return response()->json([
            'success' => true,
            'data' => CategoryWiseResource::collection($data),
        ]);
    }

    /**
     * GET /top-rated-products
     */
    public function topRatedProducts(AnalyticsRequest $request): JsonResponse
    {
        $this->authorize('view-analytics');

        $user = $request->user();
        $language = $request->input('language', config('shop.default_language', 'id'));
        $limit = (int) $request->input('limit', 10);

        $products = $this->analyticsService->topRatedProducts($user, $language, $limit);

        return response()->json([
            'success' => true,
            'data' => TopRatedProductResource::collection($products),
        ]);
    }

    /**
     * Helper untuk resolve type_id dari slug.
     */
    private function resolveTypeId(AnalyticsRequest $request): ?int
    {
        if ($request->has('type_id')) {
            return (int) $request->input('type_id');
        }

        if ($request->has('type_slug')) {
            $language = $request->input('language', config('shop.default_language', 'id'));
            $type = Type::where('slug', $request->input('type_slug'))
                ->where('language', $language)
                ->first();

            return $type?->id;
        }

        return null;
    }
}
