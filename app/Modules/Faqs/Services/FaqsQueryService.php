<?php

declare(strict_types=1);

namespace App\Modules\Faqs\Services;

use App\Enums\Permission;
use App\Models\Faqs;
use App\Models\Shop;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

final class FaqsQueryService
{
    private const CACHE_TTL_SECONDS = 3600; // 1 hour

    /**
     * @return Builder<Faqs>
     */
    public function getFaqsQuery(Request $request, ?Authenticatable $user): Builder
    {
        $language = $request->language ?? config('shop.default_language', 'id');
        $query = Faqs::with('shop')->where('language', $language);

        if (! $user) {
            // Guest users only see public (non-shop specific) FAQs unless a specific shop_id is requested
            if ($request->shop_id) {
                $query->where('shop_id', (int) $request->shop_id);
            } else {
                $query->whereNull('shop_id');
            }

            return $query;
        }

        if ($user->hasPermissionTo(Permission::SUPER_ADMIN->value)) {
            return $query;
        }

        // Apply shop filtering for store owners and staff
        if ($user->hasPermissionTo(Permission::STORE_OWNER->value)) {
            // If shop_id is requested and user owns it
            if ($request->shop_id && $this->userOwnsShop($user, (int) $request->shop_id)) {
                $query->where('shop_id', (int) $request->shop_id);
            } else {
                // Otherwise, show all FAQs for shops owned by the user
                $shopIds = $user->shops()->pluck('id')->toArray();
                $query->whereIn('shop_id', $shopIds);
            }
        } elseif ($user->hasPermissionTo(Permission::STAFF->value)) {
            // Staff can only see FAQs for their assigned shop
            if ($user->shop_id) {
                $query->where('shop_id', (int) $user->shop_id);
                // If a different shop_id is requested, deny access
                if ($request->shop_id && (int) $request->shop_id !== (int) $user->shop_id) {
                    $query->whereRaw('1 = 0'); // Return no results
                }
            } else {
                $query->whereRaw('1 = 0'); // Staff without assigned shop cannot see any FAQs
            }
        } else {
            // Customer can only see public FAQs
            $query->whereNull('shop_id');
        }

        return $query;
    }

    public function findOrFail(int $id): Faqs
    {
        return Faqs::with('shop')->findOrFail($id);
    }

    private function userOwnsShop(Authenticatable $user, int $shopId): bool
    {
        $shop = Shop::find($shopId);

        return $shop && $shop->owner_id === $user->id;
    }
}
