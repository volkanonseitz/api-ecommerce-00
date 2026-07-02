<?php

declare(strict_types=1);

namespace App\Modules\DeliveryTime\Services;

use App\Models\DeliveryTime;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;

final class DeliveryTimeQueryService
{
    private const CACHE_TTL_SECONDS = 3600; // 1 hour

    /**
     * @return Collection<int, DeliveryTime>
     */
    public function getAll(string $language): Collection
    {
        $cacheKey = "delivery_times_{$language}";

        return Cache::rememberForever(
            $cacheKey,
            function () use ($language) {
                return DeliveryTime::where('language', $language)->get();
            }
        );
    }

    public function find(string $params, string $language): DeliveryTime
    {
        $cacheKey = "delivery_time_{$params}_{$language}";

        return Cache::remember(
            $cacheKey,
            self::CACHE_TTL_SECONDS,
            function () use ($params, $language) {
                if (is_numeric($params)) {
                    return DeliveryTime::where('id', (int) $params)->where('language', $language)->firstOrFail();
                }

                return DeliveryTime::where('slug', $params)->where('language', $language)->firstOrFail();
            }
        );
    }

    public function findOrFail(int $id): DeliveryTime
    {
        return DeliveryTime::findOrFail($id);
    }
}
