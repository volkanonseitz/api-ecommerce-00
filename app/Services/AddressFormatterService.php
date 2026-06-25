<?php

namespace App\Services;

class AddressFormatterService
{
    public function format($address): string
    {
        if (! $address) {
            return '';
        }
        if (is_array($address)) {
            return implode(', ', array_filter($address));
        }
        if (is_string($address)) {
            return $address;
        }

        return '';
    }
}
