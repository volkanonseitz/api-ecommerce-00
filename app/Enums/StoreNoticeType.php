<?php

namespace App\Enums;

enum StoreNoticeType: string
{
    case ALL_VENDOR = 'all_vendor';
    case SPECIFIC_VENDOR = 'specific_vendor';
    case ALL_SHOP = 'all_shop';
    case SPECIFIC_SHOP = 'specific_shop';

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
            self::ALL_VENDOR => 'All Vendors',
            self::SPECIFIC_VENDOR => 'Specific Vendor',
            self::ALL_SHOP => 'All Shops',
            self::SPECIFIC_SHOP => 'Specific Shop',
        };
    }
}