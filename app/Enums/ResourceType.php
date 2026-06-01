<?php

namespace App\Enums;

enum ResourceType: string
{
    case DROPOFF_LOCATION = 'DROPOFF_LOCATION';
    case PICKUP_LOCATION = 'PICKUP_LOCATION';
    case PERSON = 'PERSON';
    case DEPOSIT = 'DEPOSIT';
    case FEATURES = 'FEATURES';

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
            self::DROPOFF_LOCATION => 'Drop-off Location',
            self::PICKUP_LOCATION => 'Pick-up Location',
            self::PERSON => 'Person',
            self::DEPOSIT => 'Deposit',
            self::FEATURES => 'Features',
        };
    }
}