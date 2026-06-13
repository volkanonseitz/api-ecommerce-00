<?php

namespace App\Http\Controllers;

use App\Services\AnalyticsService;
use App\Models\Type;
use Illuminate\Http\Request;
use Illuminate\Auth\AuthenticationException;

class AnalyticsController extends Controller
{
    public function __construct(private AnalyticsService $analyticsService) {}

    /**
     * GET /analytics
     */
    public function analytics(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            throw new AuthenticationException();
        }
        $data = $this->analyticsService->getAnalytics($user);
        return response()->json($data);
    }

    /**
     * GET /low-stock-products
     */
    public function lowStockProducts(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            throw new AuthenticationException();
        }
        $language = $request->language ?? config('shop.default_language', 'id');
        $typeId = $request->type_id;
        if ($request->type_slug && !$typeId) {
            $type = Type::where('slug', $request->type_slug)
                ->where('language', $language)
                ->firstOrFail();
            $typeId = $type->id;
        }
        $limit = $request->limit ?? 10;
        $products = $this->analyticsService->getLowStockProductsQuery(
            $user, $language, $typeId, $request->shop_id
        )->take($limit)->get();
        return response()->json($products);
    }

    /**
     * GET /category-wise-product
     */
    public function categoryWiseProduct(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            throw new AuthenticationException();
        }
        $language = $request->language ?? config('shop.default_language', 'id');
        $limit = $request->limit ?? 15;
        $data = $this->analyticsService->categoryWiseProductCount($user, $language, $limit);
        return response()->json($data);
    }

    /**
     * GET /category-wise-product-sale
     */
    public function categoryWiseProductSale(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            throw new AuthenticationException();
        }
        $language = $request->language ?? config('shop.default_language', 'id');
        $limit = $request->limit ?? 15;
        $data = $this->analyticsService->categoryWiseProductSales($user, $language, $limit);
        return response()->json($data);
    }

    /**
     * GET /top-rated-products
     */
    public function topRatedProducts(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            throw new AuthenticationException();
        }
        $language = $request->language ?? config('shop.default_language', 'id');
        $limit = $request->limit ?? 10;
        $data = $this->analyticsService->topRatedProducts($user, $language, $limit);
        return response()->json($data);
    }
}