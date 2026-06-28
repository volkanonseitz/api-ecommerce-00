<?php

declare(strict_types=1);

namespace App\Modules\Analytics\Services;

use App\Enums\OrderStatus;
use App\Enums\Permission;
use App\Models\Product;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ProductAnalyticsQueryService
{
    /**
     * Mendapatkan produk dengan stok rendah.
     *
     * @return Collection<int, Product>
     */
    public function getLowStockProducts(
        ?Authenticatable $user,
        string $language,
        ?int $typeId = null,
        ?int $shopId = null,
        int $limit = 10,
        int $cacheTtl = 60
    ) {
        $cacheKey = 'analytics_low_stock_'
            .($user?->id ?? 'guest')
            .'_'.$language
            .'_'.($typeId ?? 'all')
            .'_'.($shopId ?? 'all')
            .'_'.$limit;

        return Cache::remember($cacheKey, $cacheTtl, function () use ($user, $language, $typeId, $shopId, $limit) {
            return $this->buildLowStockQuery($user, $language, $typeId, $shopId)
                ->take($limit)
                ->get();
        });
    }

    /**
     * Query builder untuk low stock (dipakai juga untuk keperluan lain).
     *
     * @return Builder<Product>
     */
    public function buildLowStockQuery(
        ?Authenticatable $user,
        string $language,
        ?int $typeId = null,
        ?int $shopId = null
    ) {
        $query = Product::query()
            ->with(['shop', 'type'])
            ->where('language', $language)
            ->whereColumn('stock', '<', 'low_stock_threshold');

        // Filter tipe
        if ($typeId) {
            $query->where('type_id', $typeId);
        }

        // Filter toko
        $shopIds = $this->getShopIdsForUser($user);
        if ($shopIds !== null) {
            $query->whereIn('shop_id', $shopIds);
        }
        if ($shopId) {
            $query->where('shop_id', $shopId);
        }

        return $query;
    }

    /**
     * Kategori dengan jumlah produk terbanyak.
     *
     * @return array<int, array{ category_id: int, category_name: string, shop_name: string, product_count: int }>
     */
    public function getCategoryWiseProductCount(
        ?Authenticatable $user,
        string $language,
        int $limit = 15,
        int $cacheTtl = 300
    ): array {
        $cacheKey = 'analytics_category_count_'
            .($user?->id ?? 'guest')
            .'_'.$language
            .'_'.$limit;

        return Cache::remember($cacheKey, $cacheTtl, function () use ($user, $language, $limit) {
            return $this->categoryWiseProductCount($user, $language, $limit);
        });
    }

    /**
     * Implementasi internal tanpa cache.
     */
    private function categoryWiseProductCount(?Authenticatable $user, string $language, int $limit): array
    {
        $shopIds = $this->getShopIdsForUser($user);
        if ($shopIds === []) {
            return [];
        }

        $query = DB::table('category_product')
            ->select(
                'categories.id as category_id',
                'categories.name as category_name',
                'shops.name as shop_name',
                DB::raw('COUNT(category_product.product_id) as product_count')
            )
            ->join('products', 'category_product.product_id', '=', 'products.id')
            ->join('categories', 'category_product.category_id', '=', 'categories.id')
            ->join('shops', 'products.shop_id', '=', 'shops.id')
            ->where('categories.language', $language)
            ->groupBy('categories.id', 'categories.name', 'shops.name')
            ->orderByDesc('product_count')
            ->limit($limit);

        if ($shopIds !== null) {
            $query->whereIn('shops.id', $shopIds);
        }

        return $query->get()->toArray();
    }

    /**
     * Kategori dengan total penjualan terbanyak (berdasarkan order items).
     *
     * @return array<int, array{ category_id: int, category_name: string, total_sales: float }>
     */
    public function getCategoryWiseProductSales(
        ?Authenticatable $user,
        string $language,
        int $limit = 15,
        int $cacheTtl = 300
    ): array {
        $cacheKey = 'analytics_category_sales_'
            .($user?->id ?? 'guest')
            .'_'.$language
            .'_'.$limit;

        return Cache::remember($cacheKey, $cacheTtl, function () use ($user, $language, $limit) {
            return $this->categoryWiseProductSalesInternal($user, $language, $limit);
        });
    }

    /**
     * Internal: category wise sales.
     */
    private function categoryWiseProductSalesInternal(?Authenticatable $user, string $language, int $limit): array
    {
        $shopIds = $this->getShopIdsForUser($user);
        if ($shopIds === []) {
            return [];
        }

        $query = DB::table('category_product')
            ->select(
                'categories.id as category_id',
                'categories.name as category_name',
                DB::raw('SUM(order_items.quantity * order_items.unit_price) as total_sales')
            )
            ->join('products', 'category_product.product_id', '=', 'products.id')
            ->join('categories', 'category_product.category_id', '=', 'categories.id')
            ->join('order_items', 'products.id', '=', 'order_items.product_id')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('categories.language', $language)
            ->where('orders.order_status', OrderStatus::COMPLETED->value) // hanya order selesai
            ->groupBy('categories.id', 'categories.name')
            ->orderByDesc('total_sales')
            ->limit($limit);

        if ($shopIds !== null) {
            $query->whereIn('products.shop_id', $shopIds);
        }

        return $query->get()->toArray();
    }

    /**
     * Produk dengan rating tertinggi.
     *
     * @return Collection<int, Product>
     */
    public function getTopRatedProducts(
        ?Authenticatable $user,
        string $language,
        int $limit = 10,
        int $cacheTtl = 300
    ) {
        $cacheKey = 'analytics_top_rated_'
            .($user?->id ?? 'guest')
            .'_'.$language
            .'_'.$limit;

        return Cache::remember($cacheKey, $cacheTtl, function () use ($user, $language, $limit) {
            $shopIds = $this->getShopIdsForUser($user);

            $query = Product::query()
                ->with(['shop', 'type'])
                ->where('language', $language)
                ->whereHas('reviews', function ($q) {
                    $q->where('is_approved', true);
                })
                ->withAvg('reviews as average_rating', 'rating')
                ->orderByDesc('average_rating')
                ->limit($limit);

            if ($shopIds !== null) {
                $query->whereIn('shop_id', $shopIds);
            }

            return $query->get();
        });
    }

    /**
     * Mendapatkan daftar shop_id berdasarkan role user.
     *
     * @return int[]|null (null = semua toko, [] = tidak ada akses)
     */
    private function getShopIdsForUser(?Authenticatable $user): ?array
    {
        if (! $user) {
            return [];
        }

        if ($user->hasPermissionTo(Permission::SUPER_ADMIN->value)) {
            return null;
        }

        if ($user->hasPermissionTo(Permission::STORE_OWNER->value)) {
            return $user->shops()->pluck('shops.id')->toArray();
        }

        if ($user->hasPermissionTo(Permission::STAFF->value)) {
            return $user->shop_id ? [$user->shop_id] : [];
        }

        return [];
    }
}
