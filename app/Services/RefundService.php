<?php

declare(strict_types=1);

namespace App\Services;

use App\DTO\RefundData;
use App\Enums\Permission;
use App\Enums\RefundStatus;
use App\Models\Balance;
use App\Models\Order;
use App\Models\Refund;
use App\Models\Shop;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RefundService
{
    public function __construct(private WalletService $walletService) {}

    public function hasPermission(?Authenticatable $user, ?int $shopId = null): bool
    {
        if (! $user) {
            return false;
        }
        if ($user->hasPermissionTo(Permission::SUPER_ADMIN->value)) {
            return true;
        }
        if ($shopId) {
            $shop = Shop::find($shopId);

            return $shop && $shop->owner_id === $user->id;
        }

        return false;
    }

    public function getRefundsQuery(Request $request, Authenticatable $user)
    {
        $language = $request->language ?? config('shop.default_language', 'id');
        $query = Refund::whereHas('order', fn ($q) => $q->where('language', $language));

        if ($user->hasPermissionTo(Permission::SUPER_ADMIN->value)) {
            if (! $request->shop_id) {
                return $query->whereNull('shop_id');
            }

            return $query->where('shop_id', $request->shop_id);
        }

        if ($this->hasPermission($user, $request->shop_id)) {
            return $query->where('shop_id', $request->shop_id);
        }

        return $query->where('customer_id', $user->id)->whereNull('shop_id');
    }

    public function storeRefund(RefundData $data): Refund
    {
        return Refund::create($data->toArray());
    }

    public function updateRefund(Refund $refund, RefundData $data): Refund
    {
        $refund->update($data->toArray());

        if ($refund->status === RefundStatus::APPROVED->value) {
            $this->processApprovedRefund($refund);
        }

        return $refund->fresh();
    }

    protected function processApprovedRefund(Refund $refund): void
    {
        DB::transaction(function () use ($refund) {
            $order = Order::with('children')->find($refund->order_id);

            if (! $order) {
                return;
            }

            foreach ($order->children as $child) {
                Balance::where('shop_id', $child->shop_id)
                    ->decrement('total_earnings', $child->amount);
                Balance::where('shop_id', $child->shop_id)
                    ->decrement('current_balance', $child->amount);
            }

            $walletPoints = $this->walletService->currencyToWalletPoints($refund->amount);

            if ($walletPoints > 0) {
                $this->walletService->addPoints($refund->customer_id, $walletPoints);
            }
        });
    }

    public function deleteRefund(Refund $refund): void
    {
        $refund->delete();
    }
}
