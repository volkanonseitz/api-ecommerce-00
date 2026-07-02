<?php

declare(strict_types=1);

namespace App\Modules\StoreNotice\Services;

use App\Enums\Permission;
use App\Enums\StoreNoticeType;
use App\Models\Shop;
use App\Models\StoreNotice;
use App\Models\User;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

final class StoreNoticeQueryService
{
    /**
     * @return Builder<StoreNotice>
     */
    public function getStoreNoticesQuery(Request $request, ?Authenticatable $user): Builder
    {
        $query = StoreNotice::query()->whereDate('expired_at', '>=', Carbon::now());

        if (! $user) {
            // guest, for shop
            $shopId = $request->shop_id ?? 0;
            if ($shopId) {
                $shop = Shop::where('id', $shopId)->orWhere('slug', $shopId)->first();
                if ($shop) {
                    $query->where('created_by', $shop->owner_id)
                        ->whereHas('shops', fn ($q) => $q->where('id', $shop->id));
                }
            }

            return $query;
        }

        if ($user->hasPermissionTo(Permission::SUPER_ADMIN->value)) {
            return $query;
        }

        // authenticated non-admin
        if ($request->shop_id) {
            $shop = Shop::find($request->shop_id);
            if ($shop) {
                $query->where('created_by', $shop->owner_id)->whereHas('shops', fn ($q) => $q->where('id', $shop->id));
            }
        } elseif ($user->managed_shop) {
            $shopId = $user->managed_shop->id;
            $query->where('created_by', $user->managed_shop->owner_id)
                ->whereHas('shops', fn ($q) => $q->where('id', $shopId));
        } else {
            $query->where('created_by', $user->id)
                ->orWhereHas('users', fn ($q) => $q->where('id', $user->id));
        }

        return $query;
    }

    public function findOrFail(int $id): StoreNotice
    {
        return StoreNotice::with(['creator', 'users', 'shops', 'read_status'])->findOrFail($id);
    }

    public function getStoreNoticeTypes(?Authenticatable $user): array
    {
        if ($user && $user->hasPermissionTo(Permission::SUPER_ADMIN->value)) {
            return [
                ['name' => 'ALL VENDOR', 'value' => StoreNoticeType::ALL_VENDOR->value],
                ['name' => 'SPECIFIC VENDOR', 'value' => StoreNoticeType::SPECIFIC_VENDOR->value],
            ];
        }

        return [
            ['name' => 'ALL SHOP', 'value' => StoreNoticeType::ALL_SHOP->value],
            ['name' => 'SPECIFIC SHOP', 'value' => StoreNoticeType::SPECIFIC_SHOP->value],
        ];
    }

    public function getUsersToNotify(Request $request, ?Authenticatable $user): Collection
    {
        if ($user->hasPermissionTo(Permission::SUPER_ADMIN->value)) {
            return User::permission(Permission::STORE_OWNER->value)->orderBy('name')->get();
        }

        return $user->shops()->where('is_active', true)->get();
    }
}
