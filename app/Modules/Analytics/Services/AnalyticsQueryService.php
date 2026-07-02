<?php

declare(strict_types=1);

namespace App\Modules\Analytics\Services;

use App\Enums\Permission;
use App\Models\Shop;
use App\Models\Type;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Facades\Cache;

final class AnalyticsQueryService
{
    public function __construct(
        private readonly OrderAnalyticsQueryService $orderService,
        private readonly ProductAnalyticsQueryService $productService,
        private readonly RevenueAnalyticsQueryService $revenueService,
    ) {}

    /**
     * Dashboard analytics (dengan cache per user).
     *
     * @return array<string, mixed>
     */
    public function getAnalytics(Authenticatable $user, int $cacheTtl = 300): array
    {
        $cacheKey = 'analytics_dashboard_'.$user->id;

        return Cache::remember($cacheKey, $cacheTtl, function () use ($user) {
            return $this->buildAnalytics($user);
        });
    }

    /**
     * @return array{
     *     totalRevenue: float,
     *     totalRefunds: float,
     *     totalShops: int,
     *     totalVendors: int,
     *     todaysRevenue: float,
     *     totalOrders: int,
     *     newCustomers: int,
     *     totalYearSaleByMonth: array<int, array{month: string, total: float}>,
     *     todayTotalOrderByStatus: array,
     *     weeklyTotalOrderByStatus: array,
     *     monthlyTotalOrderByStatus: array,
     *     yearlyTotalOrderByStatus: array,
     * }
     */
    private function buildAnalytics(Authenticatable $user): array
    {
        $shopIds = $this->getShopIdsForUser($user);
        $isSuperAdmin = $user->hasPermissionTo(Permission::SUPER_ADMIN->value);

        $totalRevenue = $this->revenueService->getTotalRevenue($shopIds, $isSuperAdmin);
        $todaysRevenue = $this->revenueService->getTodaysRevenue($shopIds, $isSuperAdmin);
        $totalRefunds = $this->revenueService->getTotalRefunds($shopIds, $isSuperAdmin);
        $monthlySales = $this->revenueService->getMonthlySalesData($shopIds, $isSuperAdmin);

        $totalOrders = $this->orderService->getTotalOrders($user);
        $orderStatusesToday = $this->orderService->getOrderStatusCounts($user, 1);
        $orderStatusesWeekly = $this->orderService->getOrderStatusCounts($user, 7);
        $orderStatusesMonthly = $this->orderService->getOrderStatusCounts($user, 30);
        $orderStatusesYearly = $this->orderService->getOrderStatusCounts($user, 365);

        if ($isSuperAdmin) {
            $totalVendors = User::whereHas('permissions', fn ($q) => $q->where('name', Permission::STORE_OWNER->value))->count();
            $totalShops = Shop::count();
        } else {
            $totalShops = Shop::where('owner_id', $user->id)->count();
            $totalVendors = 0;
        }

        $newCustomers = User::permission(Permission::CUSTOMER->value)
            ->where('created_at', '>', Carbon::now()->subDays(30))
            ->count();

        return [
            'totalRevenue' => $totalRevenue,
            'totalRefunds' => $totalRefunds,
            'totalShops' => $totalShops,
            'totalVendors' => $totalVendors,
            'todaysRevenue' => $todaysRevenue,
            'totalOrders' => $totalOrders,
            'newCustomers' => $newCustomers,
            'totalYearSaleByMonth' => $monthlySales,
            'todayTotalOrderByStatus' => $orderStatusesToday,
            'weeklyTotalOrderByStatus' => $orderStatusesWeekly,
            'monthlyTotalOrderByStatus' => $orderStatusesMonthly,
            'yearlyTotalOrderByStatus' => $orderStatusesYearly,
        ];
    }

    /**
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

    public function resolveTypeId(?int $typeId, ?string $typeSlug, string $language): ?int
    {
        if ($typeId) {
            return $typeId;
        }

        if ($typeSlug) {
            $type = Type::where('slug', $typeSlug)
                ->where('language', $language)
                ->first();

            return $type?->id;
        }

        return null;
    }

    public function getLowStockProducts(
        Authenticatable $user,
        string $language,
        ?int $typeId = null,
        ?int $shopId = null,
        int $limit = 10
    ): EloquentCollection {
        return $this->productService->getLowStockProducts($user, $language, $typeId, $shopId, $limit);
    }

    public function categoryWiseProductCount(Authenticatable $user, string $language, int $limit = 15): array
    {
        return $this->productService->getCategoryWiseProductCount($user, $language, $limit);
    }

    public function categoryWiseProductSales(Authenticatable $user, string $language, int $limit = 15): array
    {
        return $this->productService->getCategoryWiseProductSales($user, $language, $limit);
    }

    public function topRatedProducts(Authenticatable $user, string $language, int $limit = 10): EloquentCollection
    {
        return $this->productService->getTopRatedProducts($user, $language, $limit);
    }
}
