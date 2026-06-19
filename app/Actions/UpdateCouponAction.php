<?php

namespace App\Actions;

use App\DTO\CouponData;
use App\Models\Coupon;

class UpdateCouponAction
{
    public function execute(Coupon $coupon, CouponData $data): Coupon
    {
        $coupon->update($data->toArray());

        return $coupon->fresh();
    }
}
