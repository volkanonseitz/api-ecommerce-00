<?php

declare(strict_types=1);

namespace App\Modules\Coupon\Actions;

use App\Models\Coupon;
use App\Modules\Coupon\DTO\CouponData;

class CreateCouponAction
{
    public function execute(CouponData $data): Coupon
    {
        return Coupon::create($data->toArray());
    }
}
