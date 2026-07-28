<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * @phpstan-type SettingsOptions array{
 *     reviewSystem?: array{
 *         value?: string
 *     },
 *     currency?: string,
 *     currencyOptions?: array{
 *         formation?: string
 *     }
 * }
 *
 * @property SettingsOptions $options
 */
class Settings extends Model
{
    use HasFactory;

    protected $table = 'settings';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'language',
        'options',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'options' => 'json',
        ];
    }

    private const CACHE_TTL_SECONDS = 3600;

    /**
     * PERFORMANCE: Settings adalah data semi-statis yang dibaca berkali-kali
     * (mis. setiap request nearByShop butuh maxShopDistance) tapi jarang berubah.
     * Di-cache via Redis untuk menghindari query berulang.
     */
    public static function getData(?string $language = null): self
    {
        $lang = $language ?? config('shop.default_language', 'id');

        return Cache::remember(
            "settings:{$lang}",
            self::CACHE_TTL_SECONDS,
            function () use ($lang): self {
                $data = static::where('language', $lang)->first();

                if (! $data) {
                    $data = static::where('language', 'id')->first();
                }

                return $data ?? new self(['options' => []]);
            }
        );
    }

    protected static function booted(): void
    {
        // Invalidasi cache setiap kali settings disimpan/dihapus, agar tidak
        // menyajikan data basi (stale cache) setelah admin mengubah konfigurasi.
        static::saved(function (self $settings): void {
            Cache::forget("settings:{$settings->language}");
        });

        static::deleted(function (self $settings): void {
            Cache::forget("settings:{$settings->language}");
        });
    }
}
