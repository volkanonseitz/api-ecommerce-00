<?php

declare(strict_types=1);

namespace App\Modules\Analytics\Http\Controllers;

use App\Http\Controllers\BaseController;
use App\Models\User;
use App\Modules\Analytics\Http\Requests\AnalyticsRequest;
use App\Modules\Analytics\Http\Resources\AnalyticsResource;
use App\Modules\Analytics\Http\Resources\CategoryWiseResource;
use App\Modules\Analytics\Http\Resources\LowStockProductResource;
use App\Modules\Analytics\Http\Resources\TopRatedProductResource;
use App\Modules\Analytics\Services\AnalyticsQueryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AnalyticsController extends BaseController
{
    public function __construct(
        private readonly AnalyticsQueryService $analyticsQueryService,
    ) {}

    /**
     * GET /analytics – Ringkasan dashboard.
     */
    public function analytics(Request $request): JsonResponse
    {
        $this->authorize('viewAny', User::class);

        $user = $request->user();
        $data = $this->analyticsQueryService->getAnalytics($user);

        return $this->sendSuccess(new AnalyticsResource($data));
    }

    /**
     * GET /low-stock-products
     */
    public function lowStockProducts(AnalyticsRequest $request): JsonResponse
    {
        $this->authorize('viewAny', User::class);

        $user = $request->user();
        $language = $request->input('language', config('shop.default_language', 'id'));
        $typeId = $this->analyticsQueryService->resolveTypeId($request->input('type_id'), $request->input('type_slug'), $language);
        $shopId = $request->input('shop_id');
        $limit = (int) $request->input('limit', 10);

        $products = $this->analyticsQueryService->getLowStockProducts(
            $user,
            $language,
            $typeId,
            $shopId,
            $limit
        );

        return $this->sendSuccess(LowStockProductResource::collection($products));
    }

    /**
     * GET /category-wise-product
     */
    public function categoryWiseProduct(AnalyticsRequest $request): JsonResponse
    {
        $this->authorize('viewAny', User::class);

        $user = $request->user();
        $language = $request->input('language', config('shop.default_language', 'id'));
        $limit = (int) $request->input('limit', 15);

        $data = $this->analyticsQueryService->categoryWiseProductCount($user, $language, $limit);

        return $this->sendSuccess(TopRatedProductResource::collection($data));
    }

    /**
     * GET /category-wise-product-sale
     */
    public function categoryWiseProductSale(AnalyticsRequest $request): JsonResponse
    {
        $this->authorize('viewAny', User::class);

        $user = $request->user();
        $language = $request->input('language', config('shop.default_language', 'id'));
        $limit = (int) $request->input('limit', 15);

        $data = $this->analyticsQueryService->categoryWiseProductSales($user, $language, $limit);

        return $this->sendSuccess(CategoryWiseResource::collection($data));
    }

    /**
     * GET /top-rated-products
     */
    public function topRatedProducts(AnalyticsRequest $request): JsonResponse
    {
        $this->authorize('viewAny', User::class);

        $user = $request->user();
        $language = $request->input('language', config('shop.default_language', 'id'));
        $limit = (int) $request->input('limit', 10);

        $products = $this->analyticsQueryService->topRatedProducts($user, $language, $limit);

        return $this->sendSuccess(CategoryWiseResource::collection($products));
    }
}
