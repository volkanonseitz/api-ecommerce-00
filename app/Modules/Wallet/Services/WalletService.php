<?php

declare(strict_types=1);

namespace App\Modules\Wallet\Services;

use App\Models\Settings;
use App\Models\Wallet;
use Illuminate\Support\Facades\DB;

class WalletService
{
    public function addPoints(int $customerId, int $points): void
    {
        if ($points <= 0) {
            return;
        }

        DB::transaction(function () use ($customerId, $points) {
            $wallet = Wallet::lockForUpdate()->firstOrCreate(['customer_id' => $customerId]);
            $wallet->increment('total_points', $points);
            $wallet->increment('available_points', $points);
        });
    }

    public function currencyToWalletPoints(float $currency): int
    {
        $ratio = $this->getCurrencyToWalletRatio();

        return (int) ($currency * $ratio);
    }

    public function walletPointsToCurrency(int $points): float
    {
        $ratio = $this->getCurrencyToWalletRatio();

        return round($points / max($ratio, 1), 2);
    }

    private function getCurrencyToWalletRatio(): float
    {
        $settings = Settings::getData();
        $ratio = $settings->options['currencyToWalletRatio'] ?? 1;

        return (float) ($ratio == 0 ? 1 : $ratio);
    }

    public function giveSignupPoints(int $customerId): void
    {
        $settings = Settings::getData();
        $points = $settings->options['signupPoints'] ?? 0;

        if ($points <= 0) {
            return;
        }

        $this->addPoints($customerId, (int) $points);
    }

    public function deductPoints(int $customerId, int $points): void
    {
        if ($points <= 0) {
            return;
        }

        DB::transaction(function () use ($customerId, $points) {
            $wallet = Wallet::lockForUpdate()->where('customer_id', $customerId)->first();

            if (! $wallet) {
                return;
            }

            $actualDeduction = min($points, $wallet->available_points);
            $wallet->decrement('available_points', $actualDeduction);
            $wallet->increment('points_used', $actualDeduction);
        });
    }
}
