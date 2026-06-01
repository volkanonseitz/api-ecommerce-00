<?php

namespace App\Enums;

enum ShippingType: string
{
    case FIXED = 'fixed';
    case PERCENTAGE = 'percentage';
    case FREE = 'free_shipping';

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
            self::FIXED => 'Fixed Amount',
            self::PERCENTAGE => 'Percentage',
            self::FREE => 'Free Shipping',
        };
    }
}