<?php

namespace App\Services;

use App\Models\Manufacturer;
use App\Models\Shop;
use App\DTO\ManufacturerData;
use App\Actions\CreateManufacturerAction;
use App\Actions\UpdateManufacturerAction;
use Illuminate\Contracts\Auth\Authenticatable;
use App\Enums\Permission;

class ManufacturerService
{
    public function __construct(
        private CreateManufacturerAction $createManufacturer,
        private UpdateManufacturerAction $updateManufacturer,
    ) {}

    public function hasPermission(?Authenticatable $user, ?int $shopId): bool
    {
        if (!$user) return false;
        if ($user->hasPermissionTo(Permission::SUPER_ADMIN->value)) return true;
        if (!$shopId) return false;

        $shop = Shop::find($shopId);
        if (!$shop || !$shop->is_active) {
            throw new \Exception(config('notice.SHOP_NOT_APPROVED'));
        }

        if ($user->hasPermissionTo(Permission::STORE_OWNER->value)) {
            return $shop->owner_id === $user->id;
        }

        return false;
    }

    public function getManufacturersByLanguage(string $language, int $perPage = 15)
    {
        return Manufacturer::where('language', $language)
            ->with('type')
            ->paginate($perPage);
    }

    public function getManufacturerByIdOrSlug($identifier, string $language): Manufacturer
    {
        if (is_numeric($identifier)) {
            return Manufacturer::with('type')->where('id', $identifier)->firstOrFail();
        }
        return Manufacturer::with('type')
            ->where('slug', $identifier)
            ->where('language', $language)
            ->firstOrFail();
    }

    public function createManufacturer(ManufacturerData $data): Manufacturer
    {
        return $this->createManufacturer->execute($data);
    }

    public function updateManufacturer(Manufacturer $manufacturer, ManufacturerData $data): Manufacturer
    {
        return $this->updateManufacturer->execute($manufacturer, $data);
    }

    public function deleteManufacturer(Manufacturer $manufacturer): void
    {
        $manufacturer->delete();
    }

    public function getTopManufacturers(string $language, int $limit = 10)
    {
        return Manufacturer::where('language', $language)
            ->withCount('products')
            ->orderBy('products_count', 'desc')
            ->take($limit)
            ->get();
    }
}