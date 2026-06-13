<?php

namespace App\Services;

use App\Models\Refund;
use App\Models\Order;
use App\Models\Balance;
use App\Models\Wallet;
use App\DTO\RefundData;
use App\Enums\Permission;
use App\Enums\RefundStatus;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\Request;

class RefundService
{
    public function __construct(private WalletService $walletService) {}

    public function hasPermission(?Authenticatable $user, ?int $shopId = null): bool
    {
        if (!$user) return false;
        if ($user->hasPermissionTo(Permission::SUPER_ADMIN->value)) return true;
        if ($shopId) {
            $shop = Shop::find($shopId);
            return $shop && $shop->owner_id === $user->id;
        }
        return false;
    }

    public function getRefundsQuery(Request $request, Authenticatable $user)
    {
        $language = $request->language ?? config('shop.default_language', 'id');
        $query = Refund::whereHas('order', fn($q) => $q->where('language', $language));

        if ($user->hasPermissionTo(Permission::SUPER_ADMIN->value)) {
            if (!$request->shop_id) return $query->whereNull('shop_id');
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
        $order = Order::find($refund->order_id);
        if ($order) {
            foreach ($order->children as $child) {
                $balance = Balance::where('shop_id', $child->shop_id)->first();
                if ($balance) {
                    $balance->total_earnings -= $child->amount;
                    $balance->current_balance -= $child->amount;
                    $balance->save();
                }
            }
        }
        $walletPoints = $this->walletService->currencyToWalletPoints($refund->amount);
        $wallet = Wallet::firstOrCreate(['customer_id' => $refund->customer_id]);
        $wallet->total_points += $walletPoints;
        $wallet->available_points += $walletPoints;
        $wallet->save();
    }

    public function deleteRefund(Refund $refund): void { $refund->delete(); }
}