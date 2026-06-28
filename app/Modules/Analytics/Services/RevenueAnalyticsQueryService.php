<?php

declare(strict_types=1);

namespace App\Modules\Analytics\Services;

use App\Enums\OrderStatus;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class RevenueAnalyticsQueryService
{
    public function getTotalRevenue(?array $shopIds, bool $isSuperAdmin, int $cacheTtl = 300): float
    {
        $cacheKey = 'analytics_revenue_total_'.($isSuperAdmin ? 'admin' : implode('_', $shopIds ?? []));

        return Cache::remember($cacheKey, $cacheTtl, function () use ($shopIds, $isSuperAdmin) {
            return $this->calculateTotalRevenue($shopIds, $isSuperAdmin);
        });
    }

    public function getTodaysRevenue(?array $shopIds, bool $isSuperAdmin, int $cacheTtl = 60): float
    {
        $cacheKey = 'analytics_revenue_today_'.($isSuperAdmin ? 'admin' : implode('_', $shopIds ?? []));

        return Cache::remember($cacheKey, $cacheTtl, function () use ($shopIds, $isSuperAdmin) {
            return $this->calculateTodaysRevenue($shopIds, $isSuperAdmin);
        });
    }

    public function getTotalRefunds(?array $shopIds, bool $isSuperAdmin, int $cacheTtl = 300): float
    {
        $cacheKey = 'analytics_refunds_'.($isSuperAdmin ? 'admin' : implode('_', $shopIds ?? []));

        return Cache::remember($cacheKey, $cacheTtl, function () use ($shopIds, $isSuperAdmin) {
            return $this->calculateTotalRefunds($shopIds, $isSuperAdmin);
        });
    }

    /**
     * @return array<int, array{month: string, total: float}>
     */
    public function getMonthlySalesData(?array $shopIds, bool $isSuperAdmin, int $cacheTtl = 300): array
    {
        $cacheKey = 'analytics_monthly_sales_'.($isSuperAdmin ? 'admin' : implode('_', $shopIds ?? []));

        return Cache::remember($cacheKey, $cacheTtl, function () use ($shopIds, $isSuperAdmin) {
            return $this->calculateMonthlySalesData($shopIds, $isSuperAdmin);
        });
    }

    private function calculateTotalRevenue(?array $shopIds, bool $isSuperAdmin): float
    {
        $query = DB::table('orders as childOrder')
            ->join('orders as parentOrder', 'childOrder.parent_id', '=', 'parentOrder.id')
            ->whereDate('childOrder.created_at', '<=', Carbon::now())
            ->whereDate('parentOrder.created_at', '<=', Carbon::now())
            ->where('childOrder.order_status', OrderStatus::COMPLETED->value)
            ->where('parentOrder.order_status', OrderStatus::COMPLETED->value)
            ->whereNotNull('childOrder.parent_id');

        if ($isSuperAdmin) {
            return (float) (clone $query)
                ->selectRaw('SUM(childOrder.paid_total + parentOrder.delivery_fee + parentOrder.sales_tax) as total')
                ->value('total') ?: 0.0;
        }

        return (float) (clone $query)
            ->whereIn('childOrder.shop_id', $shopIds ?? [])
            ->sum('childOrder.paid_total') ?: 0.0;
    }

    private function calculateTodaysRevenue(?array $shopIds, bool $isSuperAdmin): float
    {
        $query = DB::table('orders as childOrder')
            ->join('orders as parentOrder', 'childOrder.parent_id', '=', 'parentOrder.id')
            ->whereDate('childOrder.created_at', Carbon::today())
            ->where('childOrder.order_status', OrderStatus::COMPLETED->value)
            ->where('parentOrder.order_status', OrderStatus::COMPLETED->value)
            ->whereNotNull('childOrder.parent_id');

        if ($isSuperAdmin) {
            return (float) (clone $query)
                ->selectRaw('SUM(childOrder.paid_total + parentOrder.delivery_fee + parentOrder.sales_tax) as total')
                ->value('total') ?: 0.0;
        }

        return (float) (clone $query)
            ->whereIn('childOrder.shop_id', $shopIds ?? [])
            ->sum('childOrder.paid_total') ?: 0.0;
    }

    private function calculateTotalRefunds(?array $shopIds, bool $isSuperAdmin): float
    {
        $query = DB::table('refunds')->where('created_at', '<', Carbon::now());

        if ($isSuperAdmin) {
            return (float) $query->sum('amount') ?: 0.0;
        }

        return (float) $query->whereIn('shop_id', $shopIds ?? [])->sum('amount') ?: 0.0;
    }

    private function calculateMonthlySalesData(?array $shopIds, bool $isSuperAdmin): array
    {
        $currentYear = Carbon::now()->year;
        $months = [
            'January', 'February', 'March', 'April', 'May', 'June',
            'July', 'August', 'September', 'October', 'November', 'December',
        ];

        if ($isSuperAdmin) {
            $query = DB::table('orders')
                ->where('order_status', OrderStatus::COMPLETED->value)
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
                ->where('order_status', OrderStatus::COMPLETED->value)
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
