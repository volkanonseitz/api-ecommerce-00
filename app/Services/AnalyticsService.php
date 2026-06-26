<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\Permission;
use App\Models\User;
use App\Models\Shop;
use App\Services\Analytics\AnalyticsOrderService;
use App\Services\Analytics\AnalyticsProductService;
use App\Services\Analytics\AnalyticsRevenueService;
use Carbon\Carbon;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class AnalyticsService
{
    public function __construct(
        private AnalyticsRevenueService $revenue,
        private AnalyticsOrderService $orders,
        private AnalyticsProductService $products,
    ) {}

    public function getAnalytics(?Authenticatable $user, int $cacheTtl = 300): array
    {
        if (! $user) {
            return [];
        }

        $cacheKey = 'analytics_dashboard_' . $user->id;
        
        return Cache::remember($cacheKey, $cacheTtl, function () use ($user) {
            return $this->buildAnalytics($user);
        });
    }

    protected function buildAnalytics(Authenticatable $user): array
    {
        // Pemuatan ID toko yang efisien langsung dari database, bukan dari memory collection
        $shops = $user->shops()->pluck('shops.id')->toArray();
        $isSuperAdmin = $user->hasPermissionTo(Permission::SUPER_ADMIN->value);

        // Konsistensikan passing data ke sub-services (Disarankan mengoper $user jika service butuh fleksibilitas)
        $totalRevenue       = $this->revenue->getTotalRevenue($shops, $isSuperAdmin);
        $todaysRevenue      = $this->revenue->getTodaysRevenue($shops, $isSuperAdmin);
        $totalOrders        = $this->orders->getTotalOrders($shops, $isSuperAdmin);
        
        $orderStatusesToday   = $this->orders->getOrderStatusCounts($user, 1);
        $orderStatusesWeekly  = $this->orders->getOrderStatusCounts($user, 7);
        $orderStatusesMonthly = $this->orders->getOrderStatusCounts($user, 30);
        $orderStatusesYearly  = $this->orders->getOrderStatusCounts($user, 365);

        // Menghitung jumlah toko dan vendor berdasarkan role
        if ($isSuperAdmin) {
            $totalVendors = User::whereHas('permissions', fn ($q) => $q->where('name', Permission::STORE_OWNER->value))->count();
            $totalShops   = Shop::count();
        } else {
            $totalShops   = Shop::where('owner_id', $user->id)->count();
            $totalVendors = 0;
        }

        $newCustomers = User::permission(Permission::CUSTOMER->value)
            ->where('created_at', '>', Carbon::now()->subDays(30))
            ->count();

        return [
            'totalRevenue'               => $totalRevenue,
            'totalRefunds'               => $this->getTotalRefunds($shops, $isSuperAdmin),
            'totalShops'                 => $totalShops,
            'totalVendors'               => $totalVendors,
            'todaysRevenue'              => $todaysRevenue,
            'totalOrders'                => $totalOrders,
            'newCustomers'               => $newCustomers,
            'totalYearSaleByMonth'       => $this->getTotalYearSaleByMonthData($user, $shops, $isSuperAdmin), // Cache internal dihapus
            'todayTotalOrderByStatus'    => $orderStatusesToday,
            'weeklyTotalOrderByStatus'   => $orderStatusesWeekly,
            'monthlyTotalOrderByStatus'  => $orderStatusesMonthly,
            'yearlyTotalOrderByStatus'   => $orderStatusesYearly,
        ];
    }

    protected function getTotalRefunds(array $shopIds, bool $isSuperAdmin): float
    {
        $query = DB::table('refunds')->where('created_at', '<', Carbon::now());
        
        if ($isSuperAdmin) {
            // JIKA Super Admin ingin melihat TOTAL semua refund, hapus ->whereNull()
            // JIKA Super Admin hanya melihat refund sistem pusat, biarkan seperti ini
            return (float) ($query->whereNull('shop_id')->sum('amount') ?: 0.0);
        }
        
        return (float) ($query->whereIn('shop_id', $shopIds)->sum('amount') ?: 0.0);
    }

    /**
     * Mengambil data penjualan tahunan tanpa pembungkus Cache internal, 
     * karena sudah dicache bersama di tingkat dashboard.
     */
    protected function getTotalYearSaleByMonthData(Authenticatable $user, array $shopIds, bool $isSuperAdmin): array
    {
        $currentYear = Carbon::now()->year;
        $months = [
            'January', 'February', 'March', 'April', 'May', 'June',
            'July', 'August', 'September', 'October', 'November', 'December',
        ];

        if ($isSuperAdmin) {
            $query = DB::table('orders')
                ->where('order_status', \App\Enums\OrderStatus::COMPLETED->value)
                ->whereYear('created_at', $currentYear)
                ->whereNull('parent_id')
                ->select(
                    DB::raw('SUM(paid_total) as total'),
                    DB::raw("DATE_FORMAT(created_at, '%M') as month")
                );
        } else {
            if (empty($shopIds)) {
                return array_map(fn ($month) => ['month' => $month, 'total' => 0.0], $months);
            }

            $query = DB::table('orders')
                ->where('order_status', \App\Enums\OrderStatus::COMPLETED->value)
                ->whereYear('created_at', $currentYear)
                ->whereNotNull('parent_id')
                ->whereIn('shop_id', $shopIds)
                ->select(
                    DB::raw('SUM(amount) as total'),
                    DB::raw("DATE_FORMAT(created_at, '%M') as month")
                );
        }

        $totalByMonth = $query->groupBy(DB::raw("DATE_FORMAT(created_at, '%M')"))
            ->pluck('total', 'month')
            ->toArray();

        return array_map(fn ($month) => [
            'month' => $month,
            'total' => (float) ($totalByMonth[$month] ?? 0.0),
        ], $months);
    }
}