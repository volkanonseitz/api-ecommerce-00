<?php

namespace App\Services;

use App\Models\Wallet;
use App\Models\Settings;

class WalletService
{
    public function addPoints(int $customerId, int $points): void
    {
        if ($points <= 0) return;
        $wallet = Wallet::firstOrCreate(['customer_id' => $customerId]);
        $wallet->total_points += $points;
        $wallet->available_points += $points;
        $wallet->save();
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
        if ($points <= 0) return;
        $wallet = Wallet::firstOrCreate(['customer_id' => $customerId]);
        $wallet->total_points += $points;
        $wallet->available_points += $points;
        $wallet->save();
    }

    public function deductPoints(int $customerId, int $points): void
    {
        $wallet = Wallet::where('customer_id', $customerId)->first();
        if (!$wallet) return;
        $available = $wallet->available_points - $points;
        $wallet->available_points = max($available, 0);
        $wallet->points_used = ($wallet->points_used ?? 0) + min($points, $wallet->available_points + $wallet->points_used);
        $wallet->save();
    }
}