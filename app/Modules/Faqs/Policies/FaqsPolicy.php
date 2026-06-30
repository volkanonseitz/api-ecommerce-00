<?php

declare(strict_types=1);

namespace App\Modules\Faqs\Policies;

use App\Enums\Permission;
use App\Models\Faqs;
use App\Models\Shop;
use App\Models\User;

class FaqsPolicy
{
    public function viewAny(User $user): bool
    {
        return true; // semua user bisa lihat daftar FAQ
    }

    public function view(User $user, Faqs $faq): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo(Permission::SUPER_ADMIN->value)
            || $user->hasPermissionTo(Permission::STORE_OWNER->value)
            || $user->hasPermissionTo(Permission::STAFF->value);
    }

    public function update(User $user, Faqs $faq): bool
    {
        if ($user->hasPermissionTo(Permission::SUPER_ADMIN->value)) {
            return true;
        }

        // Store owner atau staff dengan shop yang sama
        if ($faq->shop_id) {
            $shop = Shop::find($faq->shop_id);
            if (! $shop) {
                return false;
            }
            if ($user->hasPermissionTo(Permission::STORE_OWNER->value) && $shop->owner_id === $user->id) {
                return true;
            }
            if ($user->hasPermissionTo(Permission::STAFF->value) && $shop->staffs->contains($user->id)) {
                return true;
            }
        }

        return false;
    }

    public function delete(User $user, Faqs $faq): bool
    {
        return $this->update($user, $faq);
    }
}
