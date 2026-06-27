<?php

declare(strict_types=1);

namespace App\Services;

class AddressFormatterService
{
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
