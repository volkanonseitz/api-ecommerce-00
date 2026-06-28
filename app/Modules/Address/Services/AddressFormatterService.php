<?php

declare(strict_types=1);

namespace App\Modules\Address\Services;

final class AddressFormatterService
{
    /**
     * @param  array<string, mixed>|string|null  $address
     */
    public function format(array|string|null $address): string
    {
        if (empty($address)) {
            return '';
        }

        if (is_array($address)) {
            return implode(', ', array_filter($address));
        }

        return (string) $address;
    }
}
