<?php

namespace App\Services;

use App\Models\OwnershipTransfer;
use App\Models\Order;
use App\Models\Shop;
use App\DTO\OwnershipTransferData;
use App\Events\OwnershipTransferStatusControl;
use App\Enums\Permission;
use App\Enums\OrderStatus;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class OwnershipTransferService
{
    /**
     * Cek permission untuk melihat/mengelola transfer
     */
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

    /**
     * Query transfer history berdasarkan user
     */
    public function getTransferHistoriesQuery(Request $request, Authenticatable $user)
    {
        if ($user->hasPermissionTo(Permission::SUPER_ADMIN->value)) {
            return OwnershipTransfer::query();
        }
        if ($user->hasPermissionTo(Permission::STORE_OWNER->value)) {
            if ($request->type === 'from') {
                return OwnershipTransfer::where('from', $user->id);
            }
            return OwnershipTransfer::where('to', $user->id);
        }
        // selain super admin dan store owner tidak boleh akses
        abort(403, config('constants.NOT_AUTHORIZED'));
    }

    /**
     * Ambil detail transfer berdasarkan transaction_identifier, dengan relasi dan info tambahan
     */
    public function getTransferDetail(string $transactionId, ?string $viewType = null): OwnershipTransfer
    {
        $transfer = OwnershipTransfer::with('shop')->where('transaction_identifier', $transactionId)->firstOrFail();

        if ($viewType === 'detail') {
            $transfer->setRelation('order_info', $this->getOrderInfo($transfer->shop_id));
            $transfer->setRelation('balance_info', $this->getBalanceInfo($transfer->shop_id));
            $transfer->setRelation('refund_info', $this->getRefundInfo($transfer->shop_id));
            $transfer->setRelation('withdrawal_info', $this->getWithdrawInfo($transfer->shop_id));
        }

        return $transfer;
    }

    /**
     * Create transfer request
     */
    public function createTransfer(OwnershipTransferData $data): OwnershipTransfer
    {
        return OwnershipTransfer::create($data->toArray());
    }

    /**
     * Update transfer status (approve/reject) - hanya super admin
     */
    public function updateTransferStatus(int $id, string $status, Authenticatable $user): OwnershipTransfer
    {
        if (!$user->hasPermissionTo(Permission::SUPER_ADMIN->value)) {
            throw new \Exception(config('constants.NOT_AUTHORIZED'));
        }

        $transfer = OwnershipTransfer::findOrFail($id);
        $shop = $transfer->shop;

        // Validasi sebelum approve
        if ($status === 'approved') {
            $this->validateTransferConditions($shop);
        }

        $transfer->status = $status;
        $transfer->save();

        event(new OwnershipTransferStatusControl($transfer));

        return $transfer;
    }

    /**
     * Validasi apakah shop eligible untuk transfer (tidak ada order belum selesai, balance <= 1, tidak ada withdrawal pending)
     */
    protected function validateTransferConditions(Shop $shop): void
    {
        $incompleteOrders = Order::where('shop_id', $shop->id)
            ->whereIn('order_status', [
                OrderStatus::PENDING->value,
                OrderStatus::PROCESSING->value,
                OrderStatus::AT_LOCAL_FACILITY->value,
                OrderStatus::OUT_FOR_DELIVERY->value,
            ])->count();

        $currentBalance = $shop->balance?->current_balance ?? 0;
        $pendingWithdrawals = $shop->withdraws->filter(fn($w) => $w->status !== 'approved')->count();

        if ($incompleteOrders > 0 || $currentBalance > 1.00 || $pendingWithdrawals > 0) {
            throw new \Exception(config('constants.COULD_NOT_SETTLE_THE_TRANSITION'));
        }
    }

    /**
     * Delete transfer request (hanya super admin atau pemilik shop)
     */
    public function deleteTransfer(int $id, Authenticatable $user): void
    {
        $transfer = OwnershipTransfer::findOrFail($id);
        if (!$this->hasPermission($user, $transfer->shop_id)) {
            throw new \Exception(config('constants.NOT_AUTHORIZED'));
        }
        $transfer->delete();
    }

    // Helper methods untuk info tambahan (mirip repository asli)
    protected function getOrderInfo(int $shopId): array
    {
        $query = DB::table('orders')
            ->whereNotNull('parent_id')
            ->whereDate('created_at', '<=', Carbon::now())
            ->where('shop_id', $shopId)
            ->select('order_status', DB::raw('count(*) as order_count'))
            ->groupBy('order_status')
            ->pluck('order_count', 'order_status')
            ->toArray();

        return [
            'pending'        => $query[OrderStatus::PENDING->value] ?? 0,
            'processing'     => $query[OrderStatus::PROCESSING->value] ?? 0,
            'complete'       => $query[OrderStatus::COMPLETED->value] ?? 0,
            'cancelled'      => $query[OrderStatus::CANCELLED->value] ?? 0,
            'refunded'       => $query[OrderStatus::REFUNDED->value] ?? 0,
            'failed'         => $query[OrderStatus::FAILED->value] ?? 0,
            'localFacility'  => $query[OrderStatus::AT_LOCAL_FACILITY->value] ?? 0,
            'outForDelivery' => $query[OrderStatus::OUT_FOR_DELIVERY->value] ?? 0,
        ];
    }

    protected function getBalanceInfo(int $shopId): ?object
    {
        return DB::table('balances')->where('shop_id', $shopId)->first();
    }

    protected function getRefundInfo(int $shopId): array
    {
        return DB::table('refunds')->where('shop_id', $shopId)->get()->toArray();
    }

    protected function getWithdrawInfo(int $shopId): array
    {
        return DB::table('withdraws')->where('shop_id', $shopId)->get()->toArray();
    }
}