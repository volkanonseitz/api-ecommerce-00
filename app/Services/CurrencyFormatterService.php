<?php

namespace App\Services;

use NumberFormatter;

class CurrencyFormatterService
{
    public function format(float $amount, string $currency = 'USD', string $locale = 'en-US'): string
    {
        $formatter = new NumberFormatter($locale, NumberFormatter::CURRENCY);

        return $formatter->formatCurrency($amount, $currency);
    }
}
