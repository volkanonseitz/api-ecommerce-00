<?php

declare(strict_types=1);

namespace App\Traits;

use App\Enums\Permission;
use App\Models\Shop;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Gate;

trait AuthorizesShopAccess
{
    /**
     * Memeriksa apakah pengguna memiliki akses ke toko tertentu.
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    protected function authorizeShop(?Authenticatable $user, ?int $shopId, string $ability = 'view'): void
    {
        if (!$user) {
            abort(401, 'Unauthenticated.');
        }

        if ($user->hasPermissionTo(Permission::SUPER_ADMIN->value)) {
            return;
        }

        if (!$shopId) {
            abort(403, 'Shop ID is required.');
        }

        $shop = Shop::find($shopId);
        if (!$shop) {
            abort(404, 'Shop not found.');
        }

        Gate::forUser($user)->authorize($ability, $shop);
    }
}