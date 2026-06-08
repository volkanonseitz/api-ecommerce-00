<?php

namespace App\Services;

use App\Models\Coupon;
use App\Models\Settings;
use App\DTO\CouponData;
use App\Actions\CreateCouponAction;
use App\Actions\UpdateCouponAction;
use App\Enums\CouponType;
use App\Enums\Permission;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CouponService
{
    public function __construct(
        private CreateCouponAction $createCoupon,
        private UpdateCouponAction $updateCoupon,
    ) {}

    /**
     * Cek permission untuk mengelola coupon (store, update, delete, approve)
     */
    public function hasPermission(?Authenticatable $user, ?int $shopId): bool
    {
        if (!$user) return false;
        if ($user->hasPermissionTo(Permission::SUPER_ADMIN->value)) return true;
        if (!$shopId) return false;

        $shop = Shop::find($shopId);
        if (!$shop) return false;

        if ($user->hasPermissionTo(Permission::STORE_OWNER->value)) {
            return $shop->owner_id === $user->id;
        }
        if ($user->hasPermissionTo(Permission::STAFF->value)) {
            return $shop->staffs->contains($user->id);
        }
        return false;
    }

    /**
     * Query builder untuk listing coupon berdasarkan user dan filter
     */
    public function getCouponsQuery(Request $request, ?Authenticatable $user): Builder
    {
        $language = $request->language ?? config('shop.default_language', 'en');
        $query = Coupon::with('shop')->whereNotNull('id');

        if ($user) {
            if ($user->hasPermissionTo(Permission::SUPER_ADMIN->value)) {
                $query->where('language', $language);
            } elseif ($user->hasPermissionTo(Permission::STORE_OWNER->value)) {
                if ($request->shop_id && $this->hasPermission($user, $request->shop_id)) {
                    $query->where('shop_id', $request->shop_id);
                } else {
                    $query->where('user_id', $user->id)->whereIn('shop_id', $user->shops->pluck('id'));
                }
                $query->where('language', $language);
            } elseif ($user->hasPermissionTo(Permission::STAFF->value)) {
                $query->where('shop_id', $request->shop_id)->where('language', $language);
            } else {
                // customer
                $query->where('language', $language);
            }
        } else {
            if ($request->shop_id) {
                $query->where('shop_id', $request->shop_id);
            }
            $query->where('language', $language);
        }

        return $query;
    }

    public function findCoupon($params, string $language): Coupon
    {
        if (is_numeric($params)) {
            return Coupon::where('id', $params)->firstOrFail();
        }
        return Coupon::where('code', $params)->where('language', $language)->firstOrFail();
    }

    public function createCoupon(CouponData $data, bool $isSuperAdmin): Coupon
    {
        $data = new CouponData(
            code: $data->code,
            language: $data->language,
            description: $data->description,
            image: $data->image,
            type: $data->type,
            amount: $data->amount,
            minimum_cart_amount: $data->minimum_cart_amount,
            active_from: $data->active_from,
            expire_at: $data->expire_at,
            target: $data->target,
            is_approve: $isSuperAdmin,
            user_id: $data->user_id,
            shop_id: $data->shop_id,
        );
        return $this->createCoupon->execute($data);
    }

    public function updateCoupon(Coupon $coupon, CouponData $data, bool $isSuperAdmin): Coupon
    {
        // Non-admin update harus mengatur is_approve menjadi false (kecuali admin)
        if (!$isSuperAdmin) {
            $data = new CouponData(
                code: $data->code,
                language: $data->language,
                description: $data->description,
                image: $data->image,
                type: $data->type,
                amount: $data->amount,
                minimum_cart_amount: $data->minimum_cart_amount,
                active_from: $data->active_from,
                expire_at: $data->expire_at,
                target: $data->target,
                is_approve: false,
                user_id: $data->user_id,
                shop_id: $data->shop_id,
            );
        }
        return $this->updateCoupon->execute($coupon, $data);
    }

    public function deleteCoupon(Coupon $coupon): void
    {
        $coupon->delete();
    }

    public function approveCoupon(Coupon $coupon): void
    {
        $coupon->is_approve = true;
        $coupon->save();
    }

    public function disapproveCoupon(Coupon $coupon): void
    {
        $coupon->is_approve = false;
        $coupon->save();
    }

    /**
     * Verify coupon logic
     */
    public function verifyCoupon(string $code, float $subTotal, ?array $items = null, ?Authenticatable $user = null): array
    {
        $coupon = Coupon::where('code', $code)->first();
        if (!$coupon) {
            return ['is_valid' => false, 'message' => config('notice.INVALID_COUPON_CODE')];
        }

        $settings = Settings::getData();
        $isFreeShippingEnabled = $settings->options['freeShipping'] ?? false;
        $freeShippingAmount = $settings->options['freeShippingAmount'] ?? 0;
        $useFreeShipping = $isFreeShippingEnabled && $freeShippingAmount <= $subTotal;

        // Approval & target check
        if (!$coupon->is_approve || (!$user && $coupon->target)) {
            $message = $coupon->is_approve ? config('notice.THIS_COUPON_CODE_IS_ONLY_FOR_VERIFIED_USERS') : config('notice.THIS_COUPON_CODE_IS_NOT_APPROVED');
            return ['is_valid' => false, 'message' => $message];
        }

        // Minimum cart amount
        if ($subTotal < $coupon->minimum_cart_amount) {
            return ['is_valid' => false, 'message' => config('notice.COUPON_CODE_IS_NOT_APPLICABLE')];
        }

        // Free shipping coupon and already free shipping active
        if ($coupon->type === CouponType::FREE_SHIPPING->value && $useFreeShipping) {
            return ['is_valid' => false, 'message' => config('notice.ALREADY_FREE_SHIPPING_ACTIVATED')];
        }

        // Shop-specific coupon validation
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
            switch ($coupon->type) {
                case CouponType::FIXED->value:
                    $isValidForShop = $isValidForShop && $totalForShop > $coupon->amount;
                    break;
                case CouponType::PERCENTAGE->value:
                    $discountAmount = ($totalForShop * $coupon->amount) / 100;
                    $isValidForShop = $isValidForShop && $totalForShop > $discountAmount;
                    break;
                case CouponType::FREE_SHIPPING->value:
                    $isValidForShop = $isValidForShop && $useFreeShipping;
                    break;
            }
            if (!$isValidForShop) {
                return ['is_valid' => false, 'message' => config('notice.COUPON_CODE_IS_NOT_APPLICABLE_IN_THIS_SHOP_PRODUCT')];
            }
        }

        // Valid
        if ($coupon->is_valid) {
            return ['is_valid' => true, 'coupon' => $coupon];
        }

        return ['is_valid' => false, 'message' => config('notice.INVALID_COUPON_CODE')];
    }
}