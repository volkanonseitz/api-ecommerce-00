<?php

namespace App\Enums;

enum Permission: string
{
    case SUPER_ADMIN = 'super_admin';
    case STORE_OWNER = 'store_owner';
    case STAFF = 'staff';
    case CUSTOMER = 'customer';

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
            self::SUPER_ADMIN => 'Super Admin',
            self::STORE_OWNER => 'Store Owner',
            self::STAFF => 'Staff',
            self::CUSTOMER => 'Customer',
        };
    }
}