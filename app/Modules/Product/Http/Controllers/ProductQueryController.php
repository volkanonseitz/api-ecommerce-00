<?php

declare(strict_types=1);

namespace App\Modules\Product\Http\Controllers;

use App\Http\Controllers\BaseController;
use App\Models\Product;
use App\Modules\Product\Http\Resources\GetSingleProductResource;
use App\Modules\Product\Http\Resources\ProductResource;
use App\Modules\Product\Services\ProductCacheService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductQueryController extends BaseController
{
    public function __construct(
        private ProductCacheService $cacheService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Product::class);
        
        $perPage = (int) $request->get('limit', 15);
        $products = $this->cacheService->getCachedProducts($request, $perPage);

        return $this->sendPaginated(
            $products,
            ProductResource::collection($products->getCollection()),
            'Products retrieved successfully'
        );
    }

    public function show(Request $request, string $identifier): JsonResponse
    {
        $product = $this->cacheService->getCachedProduct($identifier, $request);
        
        $this->authorize('view', $product);

        return $this->sendSuccess(
            new GetSingleProductResource($product),
            'Product retrieved successfully'
        );
    }

    public function showByShop(Request $request, int $shopId): JsonResponse
    {
        $this->authorize('viewAny', Product::class);
        
        $products = $this->cacheService->getCachedShopProducts($shopId, $request);

        return $this->sendPaginated(
            $products,
            ProductResource::collection($products->getCollection()),
            'Shop products retrieved successfully'
        );
    }

    public function popular(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Product::class);
        
        $products = $this->cacheService->getCachedPopularProducts($request);

        return $this->sendSuccess(
            ProductResource::collection($products),
            'Popular products retrieved successfully'
        );
    }

    public function search(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Product::class);
        
        $perPage = (int) $request->get('limit', 15);
        $products = $this->cacheService->getCachedProducts($request, $perPage);

        return $this->sendPaginated(
            $products,
            ProductResource::collection($products->getCollection()),
            'Search results retrieved successfully'
        );
    }
}