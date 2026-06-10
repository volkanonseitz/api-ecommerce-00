<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\Product;
use App\Models\Type;
use App\Models\User;
use App\Models\Shop;
use App\Enums\OrderStatus;
use App\Enums\Permission;
use Illuminate\Contracts\Auth\Authenticatable;

class AnalyticsService
{
    /**
     * Get main analytics data (dashboard)
     */
    public function getAnalytics(?Authenticatable $user): array
    {
        $shops = $user?->shops->pluck('id') ?? [];
        $isSuperAdmin = $user && $user->hasPermissionTo(Permission::SUPER_ADMIN->value);

        // Total revenue
        $totalRevenue = $this->calculateTotalRevenue($shops, $isSuperAdmin);

        // Today's revenue
        $todaysRevenue = $this->calculateTodaysRevenue($shops, $isSuperAdmin);

        // Total refunds
        $totalRefunds = $this->calculateTotalRefunds($shops, $isSuperAdmin);

        // Total orders
        $totalOrders = $this->calculateTotalOrders($shops, $isSuperAdmin);

        // Total shops & vendors
        if ($isSuperAdmin) {
            $totalVendors = User::whereHas('permissions', fn($q) => $q->where('name', Permission::STORE_OWNER->value))->count();
            $totalShops = Shop::count();
        } else {
            $totalShops = Shop::where('owner_id', $user?->id)->count();
            $totalVendors = 0;
        }

        $newCustomers = User::permission(Permission::CUSTOMER->value)
            ->whereDate('created_at', '>', Carbon::now()->subDays(30))
            ->count();

        return [
            'totalRevenue'              => $totalRevenue,
            'totalRefunds'              => $totalRefunds,
            'totalShops'                => $totalShops,
            'totalVendors'              => $totalVendors ?? 0,
            'todaysRevenue'             => $todaysRevenue,
            'totalOrders'               => $totalOrders,
            'newCustomers'              => $newCustomers,
            'totalYearSaleByMonth'      => $this->getTotalYearSaleByMonth($user),
            'todayTotalOrderByStatus'   => $this->orderCountingByStatus($user, 1),
            'weeklyTotalOrderByStatus'  => $this->orderCountingByStatus($user, 7),
            'monthlyTotalOrderByStatus' => $this->orderCountingByStatus($user, 30),
            'yearlyTotalOrderByStatus'  => $this->orderCountingByStatus($user, 365),
        ];
    }

    protected function calculateTotalRevenue($shops, bool $isSuperAdmin): float
    {
        $query = DB::table('orders as childOrder')
            ->whereDate('childOrder.created_at', '<=', Carbon::now())
            ->where('childOrder.order_status', OrderStatus::COMPLETED->value)
            ->whereNotNull('childOrder.parent_id')
            ->join('orders as parentOrder', 'childOrder.parent_id', '=', 'parentOrder.id')
            ->whereDate('parentOrder.created_at', '<=', Carbon::now())
            ->where('parentOrder.order_status', OrderStatus::COMPLETED->value)
            ->select(
                'childOrder.id',
                'childOrder.parent_id',
                'childOrder.paid_total',
                'childOrder.created_at',
                'childOrder.shop_id',
                'parentOrder.delivery_fee',
                'parentOrder.sales_tax',
            );

        if ($isSuperAdmin) {
            $results = $query->get();
            return $results->sum('paid_total')
                + $results->unique('parent_id')->sum('delivery_fee')
                + $results->unique('parent_id')->sum('sales_tax');
        } else {
            return $query->whereIn('childOrder.shop_id', $shops)->get()->sum('paid_total');
        }
    }

    protected function calculateTodaysRevenue($shops, bool $isSuperAdmin): float
    {
        $query = DB::table('orders as A')
            ->whereDate('A.created_at', '>', Carbon::now()->subDays(1))
            ->where('A.order_status', OrderStatus::COMPLETED->value)
            ->whereNotNull('A.parent_id')
            ->join('orders as B', 'A.parent_id', '=', 'B.id')
            ->where('B.order_status', OrderStatus::COMPLETED->value)
            ->select('A.id', 'A.parent_id', 'A.paid_total', 'B.delivery_fee', 'B.sales_tax', 'A.created_at', 'A.shop_id');

        if ($isSuperAdmin) {
            $results = $query->get();
            return $results->sum('paid_total')
                + $results->unique('parent_id')->sum('delivery_fee')
                + $results->unique('parent_id')->sum('sales_tax');
        } else {
            return $query->whereIn('A.shop_id', $shops)->get()->sum('paid_total');
        }
    }

    protected function calculateTotalRefunds($shops, bool $isSuperAdmin): float
    {
        $query = DB::table('refunds')->whereDate('created_at', '<', Carbon::now());
        if ($isSuperAdmin) {
            return $query->whereNull('shop_id')->sum('amount');
        }
        return $query->whereIn('shop_id', $shops)->sum('amount');
    }

    protected function calculateTotalOrders($shops, bool $isSuperAdmin): int
    {
        $query = DB::table('orders')->whereDate('created_at', '<=', Carbon::now());
        if ($isSuperAdmin) {
            return $query->whereNull('parent_id')->count();
        }
        return $query->whereIn('shop_id', $shops)->count();
    }

    /**
     * Get total sale by month for year-to-date
     */
    public function getTotalYearSaleByMonth(?Authenticatable $user): array
    {
        $months = [
            "January", "February", "March", "April", "May", "June",
            "July", "August", "September", "October", "November", "December"
        ];

        $isSuperAdmin = $user && $user->hasPermissionTo(Permission::SUPER_ADMIN->value);

        if ($isSuperAdmin) {
            $query = DB::table('orders as A')
                ->where('A.order_status', OrderStatus::COMPLETED->value)
                ->whereYear('A.created_at', Carbon::now()->year)
                ->whereNull('A.parent_id')
                ->join('orders as B', 'A.id', '=', 'B.parent_id')
                ->where('B.order_status', OrderStatus::COMPLETED->value)
                ->select(
                    DB::raw("SUM(A.paid_total) as total"),
                    DB::raw("DATE_FORMAT(A.created_at, '%M') as month")
                );
        } else {
            $shops = $user?->shops->pluck('id') ?? [];
            $query = DB::table('orders as A')
                ->where('A.order_status', OrderStatus::COMPLETED->value)
                ->whereYear('A.created_at', Carbon::now()->year)
                ->whereNotNull('A.parent_id')
                ->join('orders as B', 'A.parent_id', '=', 'B.id')
                ->whereIn('A.shop_id', $shops)
                ->select(
                    DB::raw("SUM(B.amount) as total"),
                    DB::raw("DATE_FORMAT(A.created_at, '%M') as month")
                );
        }

        $totalByMonth = $query->groupBy('month')->pluck('total', 'month')->toArray();

        return array_map(fn($month) => [
            'month' => $month,
            'total' => $totalByMonth[$month] ?? 0
        ], $months);
    }

    /**
     * Count orders by status for given days range
     */
    public function orderCountingByStatus(?Authenticatable $user, int $days): array
    {
        if (!$user) {
            return $this->emptyOrderStatusCount();
        }

        $isSuperAdmin = $user->hasPermissionTo(Permission::SUPER_ADMIN->value);
        $isStoreOwner = $user->hasPermissionTo(Permission::STORE_OWNER->value);
        $isStaff = $user->hasPermissionTo(Permission::STAFF->value);

        $query = DB::table('orders as A')
            ->whereDate('A.created_at', '>', Carbon::now()->subDays($days));

        if ($isSuperAdmin) {
            $query->whereNull('A.parent_id');
        } else {
            $query->whereNotNull('A.parent_id');
        }

        if ($isStoreOwner) {
            $shops = $user->shops->pluck('id')->toArray();
            $query->whereIn('A.shop_id', $shops);
        } elseif ($isStaff) {
            $shopId = $user->shop_id;
            if ($shopId) {
                $query->where('A.shop_id', $shopId);
            } else {
                return $this->emptyOrderStatusCount();
            }
        }

        $results = $query->select('A.order_status', DB::raw('count(*) as order_count'))
            ->groupBy('A.order_status')
            ->pluck('order_count', 'order_status')
            ->toArray();

        return [
            'pending'        => $results[OrderStatus::PENDING->value] ?? 0,
            'processing'     => $results[OrderStatus::PROCESSING->value] ?? 0,
            'complete'       => $results[OrderStatus::COMPLETED->value] ?? 0,
            'cancelled'      => $results[OrderStatus::CANCELLED->value] ?? 0,
            'refunded'       => $results[OrderStatus::REFUNDED->value] ?? 0,
            'failed'         => $results[OrderStatus::FAILED->value] ?? 0,
            'localFacility'  => $results[OrderStatus::AT_LOCAL_FACILITY->value] ?? 0,
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

    /**
     * Low stock products query
     */
    public function getLowStockProductsQuery(?Authenticatable $user, string $language, ?int $typeId = null, ?int $shopId = null)
    {
        $query = Product::with(['type', 'shop'])
            ->where('language', $language)
            ->where('quantity', '<', 10);

        if ($shopId) {
            $query->where('shop_id', $shopId);
        }

        if ($typeId) {
            $query->where('type_id', $typeId);
        }

        return $query;
    }

    /**
     * Category wise product count
     */
    public function categoryWiseProductCount(?Authenticatable $user, string $language, int $limit = 15): array
    {
        if (!$user) return [];

        $isSuperAdmin = $user->hasPermissionTo(Permission::SUPER_ADMIN->value);
        $isStoreOwner = $user->hasPermissionTo(Permission::STORE_OWNER->value);
        $isStaff = $user->hasPermissionTo(Permission::STAFF->value);

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
            ->orderBy('product_count', 'DESC')
            ->limit($limit);

        if ($isSuperAdmin) {
            // no extra filter
        } elseif ($isStoreOwner) {
            $shopIds = $user->shops->pluck('id')->toArray();
            $query->whereIn('shops.id', $shopIds);
        } elseif ($isStaff) {
            $shopId = $user->shop_id;
            if ($shopId) {
                $query->where('shops.id', $shopId);
            } else {
                return [];
            }
        } else {
            return [];
        }

        return $query->get()->toArray();
    }

    /**
     * Category wise product sales
     */
    public function categoryWiseProductSales(?Authenticatable $user, string $language, int $limit = 15): array
    {
        if (!$user) return [];

        $isSuperAdmin = $user->hasPermissionTo(Permission::SUPER_ADMIN->value);
        $isStoreOwner = $user->hasPermissionTo(Permission::STORE_OWNER->value);
        $isStaff = $user->hasPermissionTo(Permission::STAFF->value);

        $query = DB::table('categories')
            ->select(
                'categories.id as category_id',
                'categories.name as category_name',
                'shops.name as shop_name',
                DB::raw('sum(order_product.order_quantity) as total_sales')
            )
            ->leftJoin('category_product', 'category_product.category_id', '=', 'categories.id')
            ->leftJoin('products', 'category_product.product_id', '=', 'products.id')
            ->leftJoin('shops', 'products.shop_id', '=', 'shops.id')
            ->leftJoin('order_product', 'order_product.product_id', '=', 'products.id')
            ->leftJoin('orders', 'order_product.order_id', '=', 'orders.id')
            ->whereNull('orders.parent_id')
            ->where('orders.order_status', OrderStatus::COMPLETED->value)
            ->where('categories.language', $language)
            ->groupBy('categories.id', 'categories.name', 'shops.name')
            ->orderBy('total_sales', 'desc')
            ->limit($limit);

        if ($isSuperAdmin) {
            // no extra filter
        } elseif ($isStoreOwner) {
            $shopIds = $user->shops->pluck('id')->toArray();
            $query->whereIn('shops.id', $shopIds);
        } elseif ($isStaff) {
            $shopId = $user->shop_id;
            if ($shopId) {
                $query->where('shops.id', $shopId);
            } else {
                return [];
            }
        } else {
            return [];
        }

        return $query->get()->toArray();
    }

    /**
     * Top rated products
     */
    public function topRatedProducts(?Authenticatable $user, string $language, int $limit = 10): array
    {
        if (!$user) return [];

        $isSuperAdmin = $user->hasPermissionTo(Permission::SUPER_ADMIN->value);
        $isStoreOwner = $user->hasPermissionTo(Permission::STORE_OWNER->value);
        $isStaff = $user->hasPermissionTo(Permission::STAFF->value);

        $query = DB::table('reviews')
            ->join('products', 'products.id', '=', 'reviews.product_id')
            ->join('types', 'types.id', '=', 'products.type_id')
            ->select(
                'products.id as id',
                'products.name as name',
                'products.slug as slug',
                'products.price as regular_price',
                'products.sale_price as sale_price',
                'products.min_price as min_price',
                'products.max_price as max_price',
                'products.product_type as product_type',
                'products.description as description',
                'types.id as type_id',
                'types.slug as type_slug',
                DB::raw('JSON_UNQUOTE(products.image) AS image_json'),
                DB::raw('SUM(reviews.rating) as total_rating'),
                DB::raw('COUNT(reviews.id) as rating_count'),
                DB::raw('SUM(reviews.rating) / COUNT(reviews.id) as actual_rating'),
            )
            ->where('products.language', $language)
            ->groupBy(
                'products.id',
                'products.name',
                'products.slug',
                'products.price',
                'products.sale_price',
                'products.min_price',
                'products.max_price',
                'products.product_type',
                'products.description',
                'products.image',
                'types.id',
                'types.slug'
            )
            ->orderBy('actual_rating', 'desc')
            ->limit($limit);

        if ($isSuperAdmin) {
            // no extra filter
        } elseif ($isStoreOwner) {
            $shopIds = $user->shops->pluck('id')->toArray();
            $query->whereIn('products.shop_id', $shopIds);
        } elseif ($isStaff) {
            $shopId = $user->shop_id;
            if ($shopId) {
                $query->where('products.shop_id', $shopId);
            } else {
                return [];
            }
        } else {
            return [];
        }

        $results = $query->get();
        foreach ($results as $row) {
            $row->image = json_decode($row->image_json, true);
            unset($row->image_json);
        }
        return $results->toArray();
    }
}