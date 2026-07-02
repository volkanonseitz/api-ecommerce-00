<?php

declare(strict_types=1);

namespace App\Modules\Settings\Services;

use App\Events\Maintenance;
use App\Models\Settings;
use App\Models\User;
use App\Modules\Settings\DTO\SettingsData;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class SettingsService
{
    public function getApplicationSettings(): array
    {
        return [
            'last_checking_time' => Carbon::now(),
            'trust' => true,
        ];
    }

    private function getServerInfo(): array
    {
        return [
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'unknown',
            'server_time' => Carbon::now()->toDateTimeString(),
        ];
    }

    public function getSettings(string $language): ?Settings
    {
        $cacheKey = 'settings:'.$language;

        return Cache::rememberForever($cacheKey, function () use ($language) {
            $settings = Settings::where('language', $language)->first();

            if (! $settings) {
                $settings = Settings::where('language', config('shop.default_language', 'id'))->first();
            }

            return $settings;
        });
    }

    public function getSettingsWithMaintenance(string $language): array
    {
        $settingsModel = $this->getSettings($language);
        $data = $settingsModel ? $settingsModel->toArray() : [];

        if (
            isset(
                $data['options']['maintenance']['start'],
                $data['options']['maintenance']['until']
            )
        ) {
            $data['maintenance'] = [
                'start' => Carbon::parse(
                    $data['options']['maintenance']['start']
                )->format('F j, Y h:i A'),

                'until' => Carbon::parse(
                    $data['options']['maintenance']['until']
                )->format('F j, Y h:i A'),
            ];
        }

        return $data;
    }

    public function storeOrUpdate(SettingsData $data, bool $isCreation = false, ?User $user = null): Settings
    {
        $language = $data->language;
        $cacheKey = 'settings:'.$language;

        $existing = Settings::where('language', $language)->first();
        $mergedOptions = array_merge(
            $data->options,
            $this->getApplicationSettings(),
            ['server_info' => $this->getServerInfo()]
        );

        if ($existing) {
            $existing->update(['options' => $mergedOptions]);
            $settings = $existing->fresh();
        } else {
            $settings = Settings::create([
                'options' => $mergedOptions,
                'language' => $language,
            ]);
        }

        Cache::forget($cacheKey);
        event(new Maintenance($language));

        return $settings;
    }
}
