<?php

namespace App\Enums;

enum FlashSaleType: string
{
    case PERCENTAGE = 'percentage';
    case FIXED_RATE = 'fixed_rate';

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
            self::FIXED_RATE => 'Fixed Rate',
            self::PERCENTAGE => 'Percentage',
        };
    }
}