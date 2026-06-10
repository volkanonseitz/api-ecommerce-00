<?php

namespace App\Services;

use App\Models\Withdraw;
use App\Models\Balance;
use App\DTO\WithdrawData;
use App\Enums\Permission;
use App\Enums\WithdrawStatus;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class WithdrawService
{
    /**
     * Cek permission untuk mengakses withdraw
     * - Super admin bisa semua
     * - Store owner bisa untuk shop miliknya
     */
    public function hasPermission(?Authenticatable $user, ?int $shopId = null): bool
    {
        if (!$user) return false;
        if ($user->hasPermissionTo(Permission::SUPER_ADMIN->value)) return true;
        if (!$shopId) return false;

        return $user->shops->contains('id', $shopId);
    }

    /**
     * Query untuk listing withdraw berdasarkan request
     */
    public function getWithdrawsQuery(Request $request, Authenticatable $user)
    {
        $query = Withdraw::with('shop');
        $shopId = $request->shop_id ?? null;

        if ($user->hasPermissionTo(Permission::SUPER_ADMIN->value)) {
            if ($shopId) {
                $query->where('shop_id', $shopId);
            }
            return $query;
        }

        // Non-admin: hanya untuk shop yang dimiliki
        if (!$shopId) {
            throw new \Exception(config('constants.NOT_AUTHORIZED'));
        }

        if (!$this->hasPermission($user, (int)$shopId)) {
            throw new \Exception(config('constants.NOT_AUTHORIZED'));
        }

        return $query->where('shop_id', $shopId);
    }

    /**
     * Find single withdraw dengan permission check
     */
    public function findWithdraw(int $id, Authenticatable $user): Withdraw
    {
        $withdraw = Withdraw::with('shop')->findOrFail($id);
        if (!$this->hasPermission($user, $withdraw->shop_id)) {
            throw new \Exception(config('constants.NOT_AUTHORIZED'));
        }
        return $withdraw;
    }

    /**
     * Buat withdraw request
     */
    public function createWithdraw(WithdrawData $data, Authenticatable $user): Withdraw
    {
        if (!$this->hasPermission($user, $data->shop_id)) {
            throw new \Exception(config('constants.NOT_AUTHORIZED'));
        }

        $balance = Balance::where('shop_id', $data->shop_id)->first();
        if (!$balance || $balance->current_balance < $data->amount) {
            throw new BadRequestHttpException(config('constants.INSUFFICIENT_BALANCE'));
        }

        $withdraw = Withdraw::create($data->toArray());
        $withdraw->status = WithdrawStatus::PENDING->value;
        $withdraw->save();

        // Update balance
        $balance->withdrawn_amount += $data->amount;
        $balance->current_balance -= $data->amount;
        $balance->save();

        return $withdraw;
    }

    /**
     * Approve withdraw (hanya super admin)
     */
    public function approveWithdraw(int $id, string $status, Authenticatable $user): Withdraw
    {
        if (!$user->hasPermissionTo(Permission::SUPER_ADMIN->value)) {
            throw new \Exception(config('constants.NOT_AUTHORIZED'));
        }

        $withdraw = Withdraw::findOrFail($id);
        $withdraw->status = $status;
        $withdraw->save();
        return $withdraw;
    }

    /**
     * Delete withdraw (hanya super admin)
     */
    public function deleteWithdraw(int $id, Authenticatable $user): void
    {
        if (!$user->hasPermissionTo(Permission::SUPER_ADMIN->value)) {
            throw new \Exception(config('constants.NOT_AUTHORIZED'));
        }
        $withdraw = Withdraw::findOrFail($id);
        $withdraw->delete();
    }
}