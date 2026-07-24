<?php

declare(strict_types=1);

namespace App\Modules\Shop\Policies;

use App\Enums\Permission;
use App\Models\Shop;
use App\Models\User;

/**
 * Menggantikan logika otorisasi yang sebelumnya tercecer & terduplikasi di
 * ShopController (if hasPermissionTo(...) && shops->contains(...) diulang
 * 5x di method berbeda dengan kondisi yang sedikit berbeda-beda -> rawan bug
 * IDOR jika salah satu lupa di-update saat requirement berubah).
 */
class ShopPolicy
{
    public function viewAny(?User $user): bool
    {
        return true; // listing toko bersifat publik
    }

    public function view(?User $user, Shop $shop): bool
    {
        return true; // detail toko bersifat publik
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo(Permission::STORE_OWNER->value);
    }

    public function update(User $user, Shop $shop): bool
    {
        return $this->isSuperAdmin($user) || $this->isOwner($user, $shop);
    }

    public function delete(User $user, Shop $shop): bool
    {
        return $this->isSuperAdmin($user) || $this->isOwner($user, $shop);
    }

    public function manageStaff(User $user, Shop $shop): bool
    {
        return $this->isSuperAdmin($user) || $this->isOwner($user, $shop);
    }

    public function transferOwnership(User $user, Shop $shop): bool
    {
        return $this->isSuperAdmin($user) || $this->isOwner($user, $shop);
    }

    public function toggleMaintenance(User $user, Shop $shop): bool
    {
        return $this->isSuperAdmin($user) || $this->isOwner($user, $shop);
    }

    public function approve(User $user, Shop $shop): bool
    {
        return $this->isSuperAdmin($user);
    }

    /**
     * Otorisasi untuk melihat kolom finansial (`balance`) toko.
     * Dipakai di ShopResource sebagai lapis pertahanan kedua (defense in depth)
     * selain eager-loading kondisional di Service.
     */
    public function viewBalance(User $user, Shop $shop): bool
    {
        return $this->isSuperAdmin($user)
            || $this->isOwner($user, $shop)
            || $shop->staffs()->whereKey($user->id)->exists();
    }

    public function viewAdminShops(User $user): bool
    {
        return $this->isSuperAdmin($user)
            || $user->hasPermissionTo(Permission::STORE_OWNER->value)
            || $user->hasPermissionTo(Permission::STAFF->value);
    }

    private function isSuperAdmin(User $user): bool
    {
        return $user->hasPermissionTo(Permission::SUPER_ADMIN->value);
    }

    private function isOwner(User $user, Shop $shop): bool
    {
        return $user->hasPermissionTo(Permission::STORE_OWNER->value)
            && $shop->owner_id === $user->id;
    }
}
