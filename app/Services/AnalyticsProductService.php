<?php

declare(strict_types=1);

namespace App\Services\Analytics;

use App\Enums\Permission;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class AnalyticsProductService
{
    public function getCategoryWiseProductCount(?Authenticatable $user, string $language, int $limit = 15, int $cacheTtl = 300): array
    {
        $cacheKey = 'analytics_category_count_'.($user?->id ?? 'guest').'_'.$language.'_'.$limit;

        return Cache::remember($cacheKey, $cacheTtl, function () use ($user, $language, $limit) {
            return $this->categoryWiseProductCount($user, $language, $limit);
        });
    }

    public function categoryWiseProductCount(?Authenticatable $user, string $language, int $limit = 15): array
    {
        if (! $user) {
            return [];
        }

        $shopIds = $this->getShopIdsFilters($user);

        if ($shopIds === false) {
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
            ->orderByDesc('product_count') // Menggunakan helper bawaan Laravel yang lebih bersih
            ->limit($limit);

        // 4. Terapkan filter toko jika ada (Array berisi ID)
        if (is_array($shopIds)) {
            $query->whereIn('shops.id', $shopIds);
        }

        return $query->get()->toArray();
    }

    private function getShopIdsFilters(Authenticatable $user): array|null|bool
    {
        if ($user->hasPermissionTo(Permission::SUPER_ADMIN->value)) {
            return null;
        }

        if ($user->hasPermissionTo(Permission::STORE_OWNER->value)) {
            return $user->shops()->pluck('shops.id')->toArray();
        }

        if ($user->hasPermissionTo(Permission::STAFF->value)) {
            return $user->shop_id ? [$user->shop_id] : false;
        }

        return false;
    }
}
