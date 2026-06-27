<?php

declare(strict_types=1);

namespace App\Services\Analytics;

use App\Enums\OrderStatus;
use App\Enums\Permission;
use Carbon\Carbon;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class AnalyticsOrderService
{
    public function getTotalOrders(?array $shopIds, bool $isSuperAdmin, int $cacheTtl = 300): int
    {
        $cacheKey = 'analytics_orders_total_'.($isSuperAdmin ? 'admin' : implode('_', $shopIds ?? []));

        return Cache::remember($cacheKey, $cacheTtl, function () use ($shopIds, $isSuperAdmin) {
            return $this->calculateTotalOrders($shopIds, $isSuperAdmin);
        });
    }

    public function getOrderStatusCounts(?Authenticatable $user, int $days, int $cacheTtl = 60): array
    {
        $cacheKey = 'analytics_order_status_'.($user?->id ?? 'guest').'_'.$days;

        return Cache::remember($cacheKey, $cacheTtl, function () use ($user, $days) {
            return $this->orderCountingByStatus($user, $days);
        });
    }

    protected function calculateTotalOrders(?array $shopIds, bool $isSuperAdmin): int
    {
        $query = DB::table('orders')->whereDate('created_at', '<=', Carbon::now());
        if ($isSuperAdmin) {
            return $query->whereNull('parent_id')->count();
        }

        return $query->whereIn('shop_id', $shopIds ?? [])->count();
    }

    protected function orderCountingByStatus(?Authenticatable $user, int $days): array
    {
        $isSuperAdmin = $user && $user->hasPermissionTo(Permission::SUPER_ADMIN->value);
        $isStoreOwner = $user && $user->hasPermissionTo(Permission::STORE_OWNER->value);
        $isStaff = $user && $user->hasPermissionTo(Permission::STAFF->value);

        $query = DB::table('orders as A')
            ->whereDate('A.created_at', '>', Carbon::now()->subDays($days));

        if ($isSuperAdmin) {
            $query->whereNull('A.parent_id');
        } else {
            $query->whereNotNull('A.parent_id');
        }

        if ($isStoreOwner) {
            $shopIds = $user->shops->pluck('id')->toArray();
            $query->whereIn('A.shop_id', $shopIds);
        } elseif ($isStaff) {
            $shopId = $user->shop_id;
            if ($shopId) {
                $query->where('A.shop_id', $shopId);
            } else {
                return $this->emptyOrderStatusCount();
            }
        } else {
            return $this->emptyOrderStatusCount();
        }

        $results = $query->select('A.order_status', DB::raw('count(*) as order_count'))
            ->groupBy('A.order_status')
            ->pluck('order_count', 'order_status')
            ->toArray();

        return [
            'pending' => $results[OrderStatus::PENDING->value] ?? 0,
            'processing' => $results[OrderStatus::PROCESSING->value] ?? 0,
            'complete' => $results[OrderStatus::COMPLETED->value] ?? 0,
            'cancelled' => $results[OrderStatus::CANCELLED->value] ?? 0,
            'refunded' => $results[OrderStatus::REFUNDED->value] ?? 0,
            'failed' => $results[OrderStatus::FAILED->value] ?? 0,
            'localFacility' => $results[OrderStatus::AT_LOCAL_FACILITY->value] ?? 0,
            'outForDelivery' => $results[OrderStatus::OUT_FOR_DELIVERY->value] ?? 0,
        ];
    }

    protected function emptyOrderStatusCount(): array
    {
        return [
            'pending' => 0, 'processing' => 0, 'complete' => 0, 'cancelled' => 0,
            'refunded' => 0, 'failed' => 0, 'localFacility' => 0, 'outForDelivery' => 0,
        ];
    }
}
