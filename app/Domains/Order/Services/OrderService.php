<?php

declare(strict_types=1);

namespace App\Domains\Order\Services;

use App\Enums\OrderStatus;
use App\Enums\Permission;
use App\Models\Order;
use App\Models\Shop;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class OrderService
{
    public function __construct(
        private OrderInventoryService $inventoryService
    ) {}

    public function hasPermission(?Authenticatable $user, ?int $shopId): bool
    {
        if (! $user) {
            return false;
        }
        if ($user->hasPermissionTo(Permission::SUPER_ADMIN->value)) {
            return true;
        }
        if (! $shopId) {
            return false;
        }

        $shop = Shop::find($shopId);
        if (! $shop) {
            return false;
        }

        if ($user->hasPermissionTo(Permission::STORE_OWNER->value)) {
            return $shop->owner_id === $user->id;
        }
        if ($user->hasPermissionTo(Permission::STAFF->value)) {
            return $shop->staffs->contains($user->id);
        }

        return false;
    }

    /**
     * @return Builder<Order>
     */
    public function getOrdersQuery(Request $request, Authenticatable $user): Builder
    {
        if ($user->hasPermissionTo(Permission::SUPER_ADMIN->value)) {
            return Order::with('children')->whereNull('parent_id');
        }

        if ($user->hasPermissionTo(Permission::STORE_OWNER->value)) {
            if ($request->shop_id && $this->hasPermission($user, (int) $request->shop_id)) {
                return Order::with('children')->where('shop_id', $request->shop_id)->whereNotNull('parent_id');
            }
            // Optimasi Performa: Menggunakan relasi query builder shops() pluck, bukan property collection
            $shopIds = $user->shops()->pluck('shops.id')->toArray();

            return Order::with('children')->whereNotNull('parent_id')->whereIn('shop_id', $shopIds);
        }

        if ($user->hasPermissionTo(Permission::STAFF->value)) {
            if ($request->shop_id && $this->hasPermission($user, (int) $request->shop_id)) {
                return Order::with('children')->where('shop_id', $request->shop_id)->whereNotNull('parent_id');
            }

            return Order::with('children')->whereNotNull('parent_id')->where('shop_id', $user->shop_id);
        }

        return Order::with('children')->where('customer_id', $user->id)->whereNull('parent_id');
    }

    /**
     * @throws AuthorizationException
     */
    public function getOrderByTrackingOrId(string|int $param, string $language, ?Authenticatable $user = null): Order
    {
        $order = Order::where('language', $language)
            ->with(['products', 'shop', 'children.shop', 'wallet_point'])
            ->where(function ($q) use ($param) {
                $q->where('id', $param)->orWhere('tracking_number', $param);
            })->firstOrFail();

        if ($order->customer_id && $user) {
            if ($user->hasPermissionTo(Permission::SUPER_ADMIN->value)) {
                return $order;
            }
            if ($order->shop_id && $this->hasPermission($user, $order->shop_id)) {
                return $order;
            }
            if ($user->id == $order->customer_id) {
                return $order;
            }
            throw new AuthorizationException(config('notice.NOT_AUTHORIZED'));
        }

        if (! $order->customer_id) {
            return $order;
        }

        throw new AuthorizationException(config('notice.NOT_AUTHORIZED'));
    }

    public function updateOrderStatus(Order $order, string $newStatus, Authenticatable $user): Order
    {
        if ($order->shop_id && ! $this->hasPermission($user, $order->shop_id) && ! $user->hasPermissionTo(Permission::SUPER_ADMIN->value)) {
            throw new AuthorizationException(config('notice.NOT_AUTHORIZED'));
        }

        $oldStatus = $order->order_status;
        $order->order_status = $newStatus;
        $order->save();

        if ($order->parent_id === null) {
            // Optimasi: Gunakan mass update database direct ketimbang perulangan foreach
            $order->children()->update(['order_status' => $newStatus]);
        }

        if ($newStatus === OrderStatus::CANCELLED->value && $oldStatus !== OrderStatus::CANCELLED->value) {
            $this->inventoryService->restoreProductInventoryBulk($order);
        }

        return $order;
    }
}
