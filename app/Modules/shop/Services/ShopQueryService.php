<?php

declare(strict_types=1);

namespace App\Modules\Shop\Services;

use App\Models\Settings;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

final class ShopQueryService
{
    public function listQuery(): Builder
    {
        return Shop::query()
            ->withCount(['orders', 'products'])
            ->with(['owner.profile', 'ownershipHistory']);
    }

    public function findByIdOrSlug(string $identifier, ?User $user = null): Shop
    {
        $query = Shop::query()
            ->with(['categories', 'owner', 'ownershipHistory'])
            ->withCount(['orders', 'products']);

        // PERFORMANCE + SECURITY: relasi 'balance' hanya di-eager-load kalau
        // user berpotensi berhak melihatnya (dicek ulang & final di ShopResource
        // via Policy::viewBalance -- ini hanya optimasi query, bukan satu-satunya
        // lapis proteksi).
        $shop = is_numeric($identifier)
            ? $query->where('id', $identifier)->firstOrFail()
            : $query->where('slug', $identifier)->firstOrFail();

        if ($user && ($user->hasPermissionTo(\App\Enums\Permission::SUPER_ADMIN->value)
                || $user->shops()->whereKey($shop->id)->exists())) {
            $shop->load('balance');
        }

        return $shop;
    }

    public function findNewOrInactive(bool $isActive, int $perPage): LengthAwarePaginator
    {
        return Shop::query()
            ->withCount(['orders', 'products'])
            ->with(['owner.profile'])
            ->where('is_active', $isActive)
            ->paginate($perPage);
    }

    /**
     * @return Collection<int, Shop>
     */
    public function findNearby(float $lat, float $lng, ?float $maxDistanceKm = null): Collection
    {
        $maxDistance = $maxDistanceKm ?? (float) (Settings::getData()->options['maxShopDistance'] ?? 1000);

        return Shop::query()
            ->where('is_active', true)
            ->whereNotNull('settings->location->lat')
            ->whereNotNull('settings->location->lng')
            ->select('shops.*')
            ->selectRaw(
                '6371 * acos(cos(radians(?)) * cos(radians(json_unquote(json_extract(settings, "$.location.lat")))) '.
                '* cos(radians(json_unquote(json_extract(settings, "$.location.lng"))) - radians(?)) '.
                '+ sin(radians(?)) * sin(radians(json_unquote(json_extract(settings, "$.location.lat"))))) AS distance',
                [$lat, $lng, $lat]
            )
            ->having('distance', '<', $maxDistance)
            ->orderBy('distance')
            ->get();
    }

    public function isFollowing(User $user, int $shopId): bool
    {
        return $user->follow_shops()->where('shops.id', $shopId)->exists();
    }

    public function followedShops(User $user, int $perPage): LengthAwarePaginator
    {
        return $user->follow_shops()->paginate($perPage);
    }

    /**
     * @return Collection<int, \App\Models\Product>
     */
    public function followedShopsPopularProducts(User $user, int $limit): Collection
    {
        $followedIds = $user->follow_shops()->pluck('shops.id');

        return \App\Models\Product::query()
            ->withCount('orders')
            ->with('shop')
            ->whereIn('shop_id', $followedIds)
            ->orderByDesc('orders_count')
            ->take($limit)
            ->get();
    }
}
