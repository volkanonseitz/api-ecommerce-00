<?php

namespace App\Actions;

use App\Models\Coupon;
use App\DTO\CouponData;

class UpdateCouponAction
{
    public function execute(Coupon $coupon, CouponData $data): Coupon
    {
        $coupon->update($data->toArray());
        return $coupon->fresh();
    }
}