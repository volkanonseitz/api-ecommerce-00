<?php

namespace App\Enums;

enum ProductVisibilityStatus: string
{
    case VISIBILITY_PRIVATE = 'visibility_private';
    case VISIBILITY_PUBLIC = 'visibility_public';
    case VISIBILITY_PROTECTED = 'visibility_protected';

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
            self::VISIBILITY_PRIVATE => 'Private',
            self::VISIBILITY_PUBLIC => 'Public',
            self::VISIBILITY_PROTECTED => 'Protected',
        };
    }
}