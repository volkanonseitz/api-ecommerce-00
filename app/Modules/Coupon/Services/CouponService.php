<?php

declare(strict_types=1);

namespace App\Modules\Coupon\Services;

use App\Enums\CouponType;
use App\Enums\Permission;
use App\Models\Coupon;
use App\Models\Settings;
use App\Models\Shop;
use App\Modules\Coupon\Actions\CreateCouponAction;
use App\Modules\Coupon\Actions\UpdateCouponAction;
use App\Modules\Coupon\DTO\CouponData;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class CouponService
{
    public function __construct(
        private CreateCouponAction $createCoupon,
        private UpdateCouponAction $updateCoupon,
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

    public function getCouponsQuery(Request $request, ?Authenticatable $user): Builder
    {
        $language = $request->language ?? config('shop.default_language', 'id');
        $query = Coupon::with('shop')->where('language', $language);

        if ($user) {
            if ($user->hasPermissionTo(Permission::SUPER_ADMIN->value)) {
                // admin bisa lihat semua
            } elseif ($user->hasPermissionTo(Permission::STORE_OWNER->value)) {
                if ($request->shop_id && $this->hasPermission($user, $request->shop_id)) {
                    $query->where('shop_id', $request->shop_id);
                } else {
                    $query->whereIn('shop_id', $user->shops()->pluck('id'));
                }
            } elseif ($user->hasPermissionTo(Permission::STAFF->value)) {
                $query->where('shop_id', $user->shop_id);
                if ($request->shop_id && $request->shop_id != $user->shop_id) {
                    $query->whereRaw('1 = 0'); // tidak ada akses
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

    public function findCoupon($params, string $language): Coupon
    {
        if (is_numeric($params)) {
            return Coupon::where('id', $params)->firstOrFail();
        }

        return Coupon::where('code', $params)->where('language', $language)->firstOrFail();
    }

    public function createCoupon(CouponData $data, bool $isSuperAdmin): Coupon
    {
        // override is_approve sesuai role
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
        if (! $isSuperAdmin) {
            // Non-admin update akan mereset is_approve menjadi false
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
}
