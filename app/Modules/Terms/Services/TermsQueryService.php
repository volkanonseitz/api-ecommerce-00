<?php

declare(strict_types=1);

namespace App\Modules\Terms\Services;

use App\Enums\Permission;
use App\Models\Shop;
use App\Models\TermsAndConditions;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;

final class TermsQueryService
{
    /**
     * @return LengthAwarePaginator<TermsAndConditions>
     */
    public function getTermsQuery(Request $request, ?Authenticatable $user): LengthAwarePaginator
    {
        $language = $request->language ?? config('shop.default_language', 'id');
        $query = TermsAndConditions::with('shop')->where('language', $language);

        // This hasPermission logic should be handled by policy in controller.
        // For query purposes, we'll build the query based on user's role/permissions if available.
        if ($user) {
            if ($user->hasPermissionTo(Permission::SUPER_ADMIN->value)) {
                return $query->paginate($request->limit ?? 10);
            }

            if ($user->hasPermissionTo(Permission::STORE_OWNER->value)) {
                if ($request->shop_id && $this->userCanAccessShop($user, (int) $request->shop_id)) {
                    return $query->where('shop_id', (int) $request->shop_id)->paginate($request->limit ?? 10);
                }

                return $query->whereIn('shop_id', $user->shops->pluck('id'))->paginate($request->limit ?? 10);
            }

            if ($user->hasPermissionTo(Permission::STAFF->value)) {
                if ($request->shop_id && $this->userCanAccessShop($user, (int) $request->shop_id)) {
                    return $query->where('shop_id', (int) $request->shop_id)->paginate($request->limit ?? 10);
                }

                return $query->where('shop_id', $user->shop_id)->paginate($request->limit ?? 10);
            }
        }

        // Guest or customer, or authenticated user without specific roles for terms management
        if ($request->shop_id) {
            return $query->where('shop_id', (int) $request->shop_id)->where('is_approved', true)->paginate($request->limit ?? 10);
        }

        return $query->where('is_approved', true)->paginate($request->limit ?? 10);
    }

    public function find(string $slug, string $language): TermsAndConditions
    {
        return TermsAndConditions::where('slug', $slug)->where('language', $language)->firstOrFail();
    }

    public function findOrFail(int $id): TermsAndConditions
    {
        return TermsAndConditions::findOrFail($id);
    }

    private function userCanAccessShop(Authenticatable $user, int $shopId): bool
    {
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
}
