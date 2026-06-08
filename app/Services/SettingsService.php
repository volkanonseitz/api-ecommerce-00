<?php

namespace App\Services;

use App\Models\Settings;
use App\DTO\SettingsData;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class SettingsService
{
    /**
     * Get application base settings (replaces MarvelVerification)
     */
    public function getApplicationSettings(): array
    {
        // Tanpa lisensi, trust dianggap true
        return [
            'last_checking_time' => Carbon::now(),
            'trust' => true,
        ];
    }

    /**
     * Get server environment info (helper function)
     */
    private function getServerInfo(): array
    {
        return [
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'unknown',
            'server_time' => Carbon::now()->toDateTimeString(),
        ];
    }

    public function getSettings(string $language)
    {
        $cacheKey = 'cached_settings_' . $language;
        return Cache::rememberForever($cacheKey, function () use ($language) {
            $settings = Settings::where('language', $language)->first();
            if (!$settings) {
                $settings = Settings::where('language', config('shop.default_language', 'en'))->first();
            }
            return $settings;
        });
    }

    public function getSettingsWithMaintenance(string $language): array
    {
        $settings = $this->getSettings($language);
        $data = $settings ? $settings->toArray() : [];

        if ($settings && isset($settings->options['maintenance']['start'], $settings->options['maintenance']['until'])) {
            $data['maintenance'] = [
                'start' => Carbon::parse($settings->options['maintenance']['start'])->format('F j, Y h:i A'),
                'until' => Carbon::parse($settings->options['maintenance']['until'])->format('F j, Y h:i A'),
            ];
        }

        return $data;
    }

    public function storeOrUpdate(SettingsData $data, bool $isCreation = false): Settings
    {
        $language = $data->language;
        $cacheKey = 'cached_settings_' . $language;

        // Merge application settings and server info
        $mergedOptions = array_merge(
            $data->options,
            $this->getApplicationSettings(),
            ['server_info' => $this->getServerInfo()]
        );

        $existing = Settings::where('language', $language)->first();

        if ($existing) {
            Cache::forget($cacheKey);
            $existing->update(['options' => $mergedOptions]);
            $settings = $existing->fresh();
        } else {
            $settings = Settings::create([
                'options' => $mergedOptions,
                'language' => $language,
            ]);
        }

        return $settings;
    }

    public function getFirst(): ?Settings
    {
        return Settings::first();
    }
}