<?php

namespace App\Actions;

use App\Models\Coupon;
use App\DTO\CouponData;

class CreateCouponAction
{
    public function execute(CouponData $data): Coupon
    {
        return Coupon::create($data->toArray());
    }
}