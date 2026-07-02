<?php

declare(strict_types=1);

namespace App\Modules\Coupon\Actions;

use App\Models\Coupon;
use Illuminate\Support\Facades\Cache;

final class DeleteCouponAction
{
    public function execute(Coupon $coupon): void
    {
        $language = $coupon->language;
        $coupon->delete();

        // Invalidate relevant caches
        Cache::forget("coupons_{$language}_*");
    }
}
