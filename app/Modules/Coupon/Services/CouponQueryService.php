<?php

declare(strict_types=1);

namespace App\Modules\Coupon\Services;

use App\Enums\CouponType;
use App\Enums\Permission;
use App\Models\Coupon;
use App\Models\Settings;
use App\Models\Shop;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

final class CouponQueryService
{
    private const CACHE_TTL_SECONDS = 3600; // 1 hour

    /**
     * @return Builder<Coupon>
     */
    public function getCouponsQuery(Request $request, ?Authenticatable $user): Builder
    {
        $language = $request->language ?? config('shop.default_language', 'id');
        $query = Coupon::with('shop')->where('language', $language);

        if ($user) {
            if ($user->hasPermissionTo(Permission::SUPER_ADMIN->value)) {
                // admin bisa lihat semua
            } elseif ($user->hasPermissionTo(Permission::STORE_OWNER->value)) {
                if ($request->shop_id && $this->userHasPermissionToShop($user, (int) $request->shop_id)) {
                    $query->where('shop_id', (int) $request->shop_id);
                } else {
                    $query->whereIn('shop_id', $user->shops()->pluck('id'));
                }
            } elseif ($user->hasPermissionTo(Permission::STAFF->value)) {
                $query->where('shop_id', (int) $user->shop_id);
                if ($request->shop_id && (int) $request->shop_id !== (int) $user->shop_id) {
                    $query->whereRaw('1 = 0'); // user has no access to other shops
                }
            } else {
                // customer: hanya lihat coupon yang approved
                $query->where('is_approve', true);
            }
        } else {
            // guest: hanya lihat coupon yang approved
            $query->where('is_approve', true);
        }

        return $query;
    }

    public function findCoupon(string $params, string $language): Coupon
    {
        if (is_numeric($params)) {
            return Coupon::where('id', (int) $params)->firstOrFail();
        }

        return Coupon::where('code', $params)->where('language', $language)->firstOrFail();
    }

    public function findOrFail(int $id): Coupon
    {
        return Coupon::findOrFail($id);
    }

    public function verifyCoupon(string $code, float $subTotal, ?array $items = null, ?Authenticatable $user = null): array
    {
        $coupon = Coupon::where('code', $code)->first();
        if (! $coupon) {
            return ['is_valid' => false, 'message' => config('notice.INVALID_COUPON_CODE')];
        }

        $settings = Settings::getData();
        $isFreeShippingEnabled = $settings->options['freeShipping'] ?? false;
        $freeShippingAmount = $settings->options['freeShippingAmount'] ?? 0;
        $useFreeShipping = $isFreeShippingEnabled && $freeShippingAmount <= $subTotal;

        if (! $coupon->is_approve) {
            return ['is_valid' => false, 'message' => config('notice.THIS_COUPON_CODE_IS_NOT_APPROVED')];
        }

        if ($coupon->target && ! $user) {
            return ['is_valid' => false, 'message' => config('notice.THIS_COUPON_CODE_IS_ONLY_FOR_VERIFIED_USERS')];
        }

        if ($subTotal < $coupon->minimum_cart_amount) {
            return ['is_valid' => false, 'message' => config('notice.COUPON_CODE_IS_NOT_APPLICABLE')];
        }

        if ($coupon->type === CouponType::FREE_SHIPPING_COUPON->value && $useFreeShipping) {
            return ['is_valid' => false, 'message' => config('notice.ALREADY_FREE_SHIPPING_ACTIVATED')];
        }

        // Shop-specific validation
        if ($coupon->shop_id && $items) {
            $totalForShop = 0;
            foreach ($items as $item) {
                if (($item['shop_id'] ?? null) == $coupon->shop_id) {
                    $price = $item['price'] ?? $item['unit_price'] ?? 0;
                    $quantity = $item['quantity'] ?? $item['order_quantity'] ?? 1;
                    $totalForShop += $price * $quantity;
                }
            }

            $isValidForShop = $totalForShop >= $coupon->minimum_cart_amount;
            if ($coupon->type === CouponType::FIXED_COUPON->value) {
                $isValidForShop = $isValidForShop && $totalForShop > $coupon->amount;
            } elseif ($coupon->type === CouponType::PERCENTAGE_COUPON->value) {
                $discountAmount = ($totalForShop * $coupon->amount) / 100;
                $isValidForShop = $isValidForShop && $totalForShop > $discountAmount;
            } elseif ($coupon->type === CouponType::FREE_SHIPPING_COUPON->value) {
                $isValidForShop = $isValidForShop && $useFreeShipping;
            }
            if (! $isValidForShop) {
                return ['is_valid' => false, 'message' => config('notice.COUPON_CODE_IS_NOT_APPLICABLE_IN_THIS_SHOP_PRODUCT')];
            }
        }

        if (! $coupon->is_valid) {
            return ['is_valid' => false, 'message' => config('notice.INVALID_COUPON_CODE')];
        }

        return ['is_valid' => true, 'coupon' => $coupon];
    }

    private function userHasPermissionToShop(?Authenticatable $user, int $shopId): bool
    {
        if (! $user) {
            return false;
        }
        $shop = Shop::find($shopId);
        if (! $shop) {
            return false;
        }

        return $shop->owner_id === $user->id;
    }
}
