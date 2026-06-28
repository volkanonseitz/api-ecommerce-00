<?php

declare(strict_types=1);

namespace App\Modules\Product\Services;

use App\Models\Product;
use App\Models\Type;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;

class ProductMetricService
{
    /**
     * @return Collection<int, Product>
     */
    public function getBestSellingProducts(Request $request): Collection
    {
        $limit = $request->limit ?? 10;
        $language = $request->language ?? config('shop.default_language', 'id');
        $range = $request->range ?? '';
        $typeId = $this->resolveTypeId($request, $language);

        $query = Product::select('products.*')
            ->join('order_product', 'order_product.product_id', 'products.id')
            ->join('orders', 'order_product.order_id', '=', 'orders.id')
            ->selectRaw('products.*, sum(order_product.order_quantity) as total_sales')
            ->whereNull('orders.parent_id')
            ->where('orders.order_status', 'order-completed')
            ->where('orders.language', $language)
            ->groupBy('products.id')
            ->orderBy('total_sales', 'desc');

        if ($request->filled('shop_id')) {
            $query->where('products.shop_id', $request->shop_id);
        }
        if ($range) {
            $query->whereDate('products.created_at', '>', Carbon::now()->subDays((int) $range));
        }
        if ($typeId) {
            $query->where('products.type_id', $typeId);
        }

        return $query->take((int) $limit)->get();
    }

    /**
     * @return Collection<int, Product>
     */
    public function getPopularProducts(Request $request): Collection
    {
        $limit = $request->limit ?? 10;
        $language = $request->language ?? config('shop.default_language', 'id');
        $range = $request->range ?? '';
        $typeId = $this->resolveTypeId($request, $language);

        $query = Product::withCount('orders')
            ->with(['type', 'shop'])
            ->orderBy('orders_count', 'desc')
            ->where('language', $language);

        if ($request->filled('shop_id')) {
            $query->where('shop_id', $request->shop_id);
        }
        if ($range) {
            $query->whereDate('created_at', '>', Carbon::now()->subDays((int) $range));
        }
        if ($typeId) {
            $query->where('type_id', $typeId);
        }

        return $query->take((int) $limit)->get();
    }

    private function resolveTypeId(Request $request, string $language): ?int
    {
        if ($request->filled('type_id')) {
            return (int) $request->type_id;
        }
        if ($request->filled('type_slug')) {
            $type = Type::where('slug', $request->type_slug)
                ->where('language', $language)
                ->first();

            return $type?->id;
        }

        return null;
    }
}
