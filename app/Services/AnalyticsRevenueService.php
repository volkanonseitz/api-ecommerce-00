<?php

declare(strict_types=1);

namespace App\Services\Analytics;

use App\Enums\OrderStatus;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class AnalyticsRevenueService
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

    protected function calculateTotalRevenue(?array $shopIds, bool $isSuperAdmin): float
    {
        $query = DB::table('orders as childOrder')
            ->join('orders as parentOrder', 'childOrder.parent_id', '=', 'parentOrder.id')
            ->whereDate('childOrder.created_at', '<=', Carbon::now())
            ->whereDate('parentOrder.created_at', '<=', Carbon::now())
            ->where('childOrder.order_status', OrderStatus::COMPLETED->value)
            ->where('parentOrder.order_status', OrderStatus::COMPLETED->value)
            ->whereNotNull('childOrder.parent_id');

        if ($isSuperAdmin) {
            return (clone $query)
                ->selectRaw('SUM(childOrder.paid_total + parentOrder.delivery_fee + parentOrder.sales_tax) as total')
                ->value('total') ?: 0.0;
        }

        return (clone $query)
            ->whereIn('childOrder.shop_id', $shopIds ?? [])
            ->sum('childOrder.paid_total') ?: 0.0;
    }

    protected function calculateTodaysRevenue(?array $shopIds, bool $isSuperAdmin): float
    {
        $query = DB::table('orders as childOrder')
            ->join('orders as parentOrder', 'childOrder.parent_id', '=', 'parentOrder.id')
            ->whereDate('childOrder.created_at', '>', Carbon::now()->subDay())
            ->where('childOrder.order_status', OrderStatus::COMPLETED->value)
            ->where('parentOrder.order_status', OrderStatus::COMPLETED->value)
            ->whereNotNull('childOrder.parent_id');

        if ($isSuperAdmin) {
            return (clone $query)
                ->selectRaw('SUM(childOrder.paid_total + parentOrder.delivery_fee + parentOrder.sales_tax) as total')
                ->value('total') ?: 0.0;
        }

        return (clone $query)
            ->whereIn('childOrder.shop_id', $shopIds ?? [])
            ->sum('childOrder.paid_total') ?: 0.0;
    }
}
