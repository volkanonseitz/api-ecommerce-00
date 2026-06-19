<?php

namespace App\Actions;

use App\DTO\CouponData;
use App\Models\Coupon;

class CreateCouponAction
{
    public function execute(CouponData $data): Coupon
    {
        return Coupon::create($data->toArray());
    }
}
