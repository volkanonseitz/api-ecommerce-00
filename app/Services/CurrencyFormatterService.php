<?php

namespace App\Services;

use NumberFormatter;

class CurrencyFormatterService
{
    public function format($amount, string $currency = 'USD', string $locale = 'en-US'): string
    {
        if ($amount === null) {
            return '';
        }
        $formatter = new NumberFormatter($locale, NumberFormatter::CURRENCY);

        return $formatter->formatCurrency($amount, $currency);
    }
}
