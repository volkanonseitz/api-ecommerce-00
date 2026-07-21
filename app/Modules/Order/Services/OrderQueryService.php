<?php

declare(strict_types=1);

namespace App\Modules\Order\Services;

use App\Enums\Permission;
use App\Models\Order;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class OrderQueryService
{
    private const DEFAULT_RELATIONS = [
        'children.shop',
        'children.products',
        'products',
        'shop',
        'customer',
        'coupon',
        'wallet_point',
        'refund',
        'payment_intent',
    ];

    private const PUBLIC_RELATIONS = [
        'children.shop',
        'children.products',
        'products',
        'shop',
    ];

    public function buildQuery(Request $request, User $user): Builder
    {
        $query = Order::query();

        // Authorization filter
        $this->applyAuthorizationFilter($query, $user);

        // Eager loading
        $this->applyEagerLoading($query, $user);

        // Filters
        $this->applyFilters($query, $request);

        // Sorting
        $this->applySorting($query, $request);

        return $query;
    }

    public function getPaginatedOrders(Request $request, User $user, int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->buildQuery($request, $user);

        return $query->paginate($perPage);
    }

    public function getSingleOrder(string $identifier, Request $request, User $user): Order
    {
        $query = $this->buildQuery($request, $user);

        if (is_numeric($identifier)) {
            return $query->findOrFail((int) $identifier);
        }

        return $query->where('tracking_number', $identifier)->firstOrFail();
    }

    public function getOrdersByShop(int $shopId, Request $request, User $user): LengthAwarePaginator
    {
        $query = $this->buildQuery($request, $user);
        $query->where('shop_id', $shopId);

        return $query->paginate($request->get('limit', 15));
    }

    public function getOrderStats(User $user, ?int $shopId = null): array
    {
        $query = Order::query();

        if (! $user->hasPermissionTo(Permission::SUPER_ADMIN->value)) {
            $this->applyAuthorizationFilter($query, $user);
        }

        if ($shopId) {
            $query->where('shop_id', $shopId);
        }

        // Satu aggregate query untuk semua statistik
        $stats = (clone $query)
            ->selectRaw("
                COUNT(*) as total,
                SUM(CASE WHEN order_status = 'order-pending' THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN order_status = 'order-processing' THEN 1 ELSE 0 END) as processing,
                SUM(CASE WHEN order_status = 'order-completed' THEN 1 ELSE 0 END) as completed,
                SUM(CASE WHEN order_status = 'order-cancelled' THEN 1 ELSE 0 END) as cancelled,
                SUM(CASE WHEN payment_status = 'refunded' THEN 1 ELSE 0 END) as refunded
            ")
            ->first();

        return [
            'total' => (int) ($stats->total ?? 0),
            'pending' => (int) ($stats->pending ?? 0),
            'processing' => (int) ($stats->processing ?? 0),
            'completed' => (int) ($stats->completed ?? 0),
            'cancelled' => (int) ($stats->cancelled ?? 0),
            'refunded' => (int) ($stats->refunded ?? 0),
        ];
    }

    private function applyAuthorizationFilter(Builder $query, User $user): void
    {
        // Super admin can see all orders
        if ($user->hasPermissionTo(Permission::SUPER_ADMIN->value)) {
            $query->whereNull('parent_id'); // Only parent orders

            return;
        }

        // Store owners and staff see their shop orders
        if ($user->hasPermissionTo(Permission::STORE_OWNER->value)) {
            $shopIds = $user->shops()->pluck('shops.id')->toArray();
            $query->whereIn('shop_id', $shopIds)->whereNotNull('parent_id');

            return;
        }

        if ($user->hasPermissionTo(Permission::STAFF->value)) {
            $query->where('shop_id', $user->shop_id)->whereNotNull('parent_id');

            return;
        }

        // Regular customers see their own orders
        $query->where('customer_id', $user->id)->whereNull('parent_id');
    }

    private function applyEagerLoading(Builder $query, User $user): void
    {
        $relations = $user->hasPermissionTo(Permission::SUPER_ADMIN->value)
            ? self::DEFAULT_RELATIONS
            : self::PUBLIC_RELATIONS;

        $query->with($relations);
    }

    use Illuminate\Support\Facades\DB;

    // ...

    private function applyFilters(Builder $query, Request $request): void
    {
        // Search filter
        if ($search = $request->get('search')) {
            $search = trim($search);
            if (empty($search)) {
                return; // Jangan lakukan pencarian jika string kosong
            }

            // ponytail: FULLTEXT index hanya didukung oleh MySQL/MariaDB.
            // Untuk SQLite, ini akan fallback ke LIKE, yang performanya tidak optimal.
            if (DB::getDriverName() == 'mysql') {
                $searchQuery = "+{$search}*"; // Match kata yang diawali dengan $search

                $query->where(function ($q) use ($searchQuery) {
                    // Pencarian di kolom tracking_number
                    $q->whereRaw('MATCH(tracking_number) AGAINST(? IN BOOLEAN MODE)', [$searchQuery]);

                    // Pencarian di kolom name dan email customer
                    $q->orWhereHas('customer', function ($customerQuery) use ($searchQuery) {
                        $customerQuery->whereRaw('MATCH(name, email) AGAINST(? IN BOOLEAN MODE)', [$searchQuery]);
                    });
                });
            } else {
                // Fallback untuk driver database lain (misal SQLite)
                $query->where(function ($q) use ($search) {
                    $q->where('tracking_number', 'like', "%{$search}%")
                        ->orWhereHas('customer', function ($customerQuery) use ($search) {
                            $customerQuery->where('name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%");
                        });
                });
            }
        }

        // Order status filter
        if ($status = $request->get('status')) {
            $query->where('order_status', $status);
        }

        // Payment status filter
        if ($paymentStatus = $request->get('payment_status')) {
            $query->where('payment_status', $paymentStatus);
        }

        // Date range filter
        if ($startDate = $request->get('start_date')) {
            $query->whereDate('created_at', '>=', $startDate);
        }
        if ($endDate = $request->get('end_date')) {
            $query->whereDate('created_at', '<=', $endDate);
        }

        // Amount range filter
        if ($minAmount = $request->get('min_amount')) {
            $query->where('total', '>=', $minAmount);
        }
        if ($maxAmount = $request->get('max_amount')) {
            $query->where('total', '<=', $maxAmount);
        }

        // Shop filter
        if ($shopId = $request->get('shop_id')) {
            $query->where('shop_id', $shopId);
        }
    }

    private function applySorting(Builder $query, Request $request): void
    {
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');

        $validSortColumns = [
            'created_at', 'updated_at', 'total',
            'order_status', 'payment_status',
        ];

        if (in_array($sortBy, $validSortColumns, true)) {
            $query->orderBy($sortBy, $sortOrder);
        }
    }
}
