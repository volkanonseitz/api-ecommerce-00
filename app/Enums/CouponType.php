<?php

namespace App\Enums;

enum CouponType: string
{
    case FIXED_COUPON = 'fixed';
    case PERCENTAGE_COUPON = 'percentage';
    case FREE_SHIPPING_COUPON = 'free_shipping';

    /**
     * Get all values for database enum
     */
    public static function getValues(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Get label for display
     */
    public function label(): string
    {
        return match($this) {
            self::FIXED_COUPON => 'Fixed Coupon',
            self::PERCENTAGE_COUPON => 'Percentage Coupon',
            self::FREE_SHIPPING_COUPON => 'Free Shipping Coupon',
        };
    }
}