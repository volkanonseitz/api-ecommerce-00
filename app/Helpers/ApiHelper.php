<?php

if (! function_exists('formatAPIResourcePaginate')) {
    function formatAPIResourcePaginate($data)
    {
        return $data;
    }
}

if (! function_exists('format_currency')) {
    function format_currency($amount, $currency = 'USD', $locale = 'id-ID')
    {
        if ($amount === null) {
            return '';
        }
        $formatter = new NumberFormatter($locale, NumberFormatter::CURRENCY);

        return $formatter->formatCurrency($amount, $currency);
    }
}
