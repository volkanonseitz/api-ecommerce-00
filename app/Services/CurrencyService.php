<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Currency;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

final class CurrencyService
{
    public function getDefaultCurrency(): Currency
    {
        return Currency::default()->active()->firstOr(fn () => Currency::where('code', 'IDR')->first());
    }

    public function convert(float $amount, string $from, string $to): float
    {
        if ($from === $to) {
            return $amount;
        }

        $fromCurrency = Currency::where('code', $from)->first();
        $toCurrency = Currency::where('code', $to)->first();

        if (! $fromCurrency || ! $toCurrency) {
            return $amount;
        }

        // Convert to default first, then to target
        $default = $this->getDefaultCurrency();
        $amountInDefault = $amount / $fromCurrency->exchange_rate;

        return round($amountInDefault * $toCurrency->exchange_rate, 2);
    }

    public function format(float $amount, string $currencyCode): string
    {
        $currency = Currency::where('code', $currencyCode)->first();
        $symbol = $currency?->symbol ?? $currencyCode;

        return $symbol.number_format($amount, 2, ',', '.');
    }

    public function updateRates(): void
    {
        try {
            // Using a free exchange rate API
            $response = Http::get('https://api.exchangerate-api.com/v4/latest/USD');

            if ($response->successful()) {
                $rates = $response->json('rates');
                $baseCode = $response->json('base');

                if ($rates && $baseCode) {
                    $default = $this->getDefaultCurrency();

                    foreach ($rates as $code => $rate) {
                        $currency = Currency::where('code', $code)->first();
                        if ($currency) {
                            // Normalize rate relative to default currency
                            $normalizedRate = $default->code === 'USD'
                                ? $rate
                                : $rate / ($rates[$default->code] ?? 1);

                            $currency->update([
                                'exchange_rate' => $normalizedRate,
                                'rate_updated_at' => now(),
                            ]);
                        }
                    }
                }
            }
        } catch (\Throwable $e) {
            Log::error('Failed to update currency rates: '.$e->getMessage());
        }
    }
}
