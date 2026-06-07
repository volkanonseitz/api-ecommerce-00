<?php

namespace App\Services;

use App\Models\Shop;
use App\Models\User;
use App\Models\Product;
use App\Models\Balance;
use App\Models\OwnershipTransfer;
use App\DTO\ShopData;
use App\Actions\CreateShopAction;
use App\Actions\UpdateShopAction;
use App\Enums\Permission;
use App\Enums\DefaultStatusType;
use App\Events\ShopMaintenance;
use App\Events\ProcessOwnershipTransition;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request; // FIX: Import ditambahkan disini

class ShopService
{
    public function __construct(
        private CreateShopAction $createShop,
        private UpdateShopAction $updateShop,
    ) {}

    public function hasPermission(?Authenticatable $user, ?int $shopId): bool
    {
        if (!$user) return false;
        if ($user->hasPermissionTo(Permission::SUPER_ADMIN->value)) return true;
        if (!$shopId) return false;

        $shop = Shop::find($shopId);
        if (!$shop) return false;

        if ($user->hasPermissionTo(Permission::STORE_OWNER->value)) {
            return $shop->owner_id === $user->id;
        }
        if ($user->hasPermissionTo(Permission::STAFF->value)) {
            return $shop->staffs->contains($user->id);
        }
        return false;
    }

    public function getShopsQuery(Request $request): Builder
    {
        return Shop::withCount(['orders', 'products'])
            ->with(['owner.profile', 'ownership_history']);
    }

    public function getShopByIdOrSlug($identifier, ?Authenticatable $user = null)
    {
        $query = Shop::with(['categories', 'owner', 'ownership_history'])
            ->withCount(['orders', 'products']);

        if ($user && ($user->hasPermissionTo(Permission::SUPER_ADMIN->value) || $user->shops->contains('slug', $identifier))) {
            $query->with('balance');
        }

        if (is_numeric($identifier)) {
            return $query->where('id', $identifier)->firstOrFail();
        }
        return $query->where('slug', $identifier)->firstOrFail();
    }

    public function createShop(ShopData $data): Shop
    {
        return $this->createShop->execute($data);
    }

    public function updateShop(Shop $shop, ShopData $data): Shop
    {
        return $this->updateShop->execute($shop, $data);
    }

    public function deleteShop(Shop $shop): void
    {
        $shop->delete();
    }

    public function approveShop(Shop $shop, ?float $adminCommissionRate, bool $isCustomCommission): void
    {
        $shop->is_active = true;
        $shop->save();

        Product::where('shop_id', $shop->id)->update(['status' => 'publish']);

        $balance = Balance::firstOrNew(['shop_id' => $shop->id]);
        if (!$isCustomCommission) {
            $defaultRate = $shop->getCommissionRate($balance->total_earnings ?? 0);
            $balance->admin_commission_rate = $defaultRate;
        } else {
            $balance->admin_commission_rate = $adminCommissionRate;
        }
        $balance->is_custom_commission = $isCustomCommission;
        $balance->save();
    }

    public function disapproveShop(Shop $shop): void
    {
        $shop->is_active = false;
        $shop->save();
        Product::where('shop_id', $shop->id)->update(['status' => 'draft']);
    }

    public function addStaff(Shop $shop, array $staffData): User
    {
        $user = User::create([
            'name' => $staffData['name'],
            'email' => $staffData['email'],
            'password' => bcrypt($staffData['password']),
            'shop_id' => $shop->id,
        ]);
        $user->givePermissionTo(Permission::CUSTOMER->value, Permission::STAFF->value);
        $user->assignRole(\App\Enums\Role::STAFF->value);
        return $user;
    }

    public function removeStaff(User $staff): void
    {
        $staff->delete();
    }

    public function transferShopOwnership(Shop $shop, User $newOwner, User $initiator, ?string $message, ?string $vendorMessage): void
    {
        OwnershipTransfer::updateOrCreate(
            ['shop_id' => $shop->id],
            [
                'from' => $shop->owner_id,
                'to' => $newOwner->id,
                'message' => $message,
                'created_by' => $initiator->id,
                'status' => DefaultStatusType::PENDING,
            ]
        );
        event(new ProcessOwnershipTransition($shop, $shop->owner, $newOwner, ['message' => $vendorMessage]));
    }

    public function enableMaintenance(Shop $shop): void
    {
        Product::where('shop_id', $shop->id)->update(['visibility' => 'private']);
        event(new ShopMaintenance($shop, 'start'));
    }

    public function disableMaintenance(Shop $shop): void
    {
        Product::where('shop_id', $shop->id)->update(['visibility' => 'public']);
        event(new ShopMaintenance($shop, 'disable'));
    }

    public function toggleFollowShop(User $user, int $shopId): bool
    {
        $followed = $user->follow_shops()->pluck('id')->toArray();
        if (in_array($shopId, $followed)) {
            $user->follow_shops()->detach($shopId);
            return false;
        } else {
            $user->follow_shops()->attach($shopId);
            return true;
        }
    }

    public function getUserFollowedShops(User $user, int $perPage = 15)
    {
        return $user->follow_shops()->paginate($perPage);
    }

    public function isUserFollowingShop(User $user, int $shopId): bool
    {
        return $user->follow_shops()->where('shop_id', $shopId)->exists();
    }

    public function getFollowedShopsPopularProducts(User $user, int $limit = 10)
    {
        $followedIds = $user->follow_shops()->pluck('shops.id')->toArray();
        return Product::withCount('orders')
            ->with('shop')
            ->whereIn('shop_id', $followedIds)
            ->orderBy('orders_count', 'desc')
            ->take($limit)
            ->get();
    }

    public function findNearbyShops(float $lat, float $lng, float $maxDistance = 1000)
    {
        return Shop::where('is_active', true)
            ->whereNotNull('settings->location->lat')
            ->whereNotNull('settings->location->lng')
            ->select('shops.*')
            ->selectRaw(
                "6371 * acos(cos(radians(?)) * cos(radians(json_unquote(json_extract(settings, '$.location.lat')))) * cos(radians(json_unquote(json_extract(settings, '$.location.lng'))) - radians(?)) + sin(radians(?)) * sin(radians(json_unquote(json_extract(settings, '$.location.lat'))))) AS distance",
                [$lat, $lng, $lat]
            )
            ->having('distance', '<', $maxDistance)
            ->orderBy('distance')
            ->get();
    }
}