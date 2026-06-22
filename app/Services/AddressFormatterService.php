<?php

namespace App\Services;

class AddressFormatterService
{
    public function formatAddress($address): string
    {
        if (is_null($address)) {
            return '';
        }
        if (is_string($address)) {
            return $address;
        }
        if (is_array($address)) {
            return implode(', ', array_filter($address));
        }

        return '';
    }
}
