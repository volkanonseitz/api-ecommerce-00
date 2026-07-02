<?php

declare(strict_types=1);

namespace App\Modules\Shop\Actions;

use App\Models\User;

final class ToggleFollowShopAction
{
    public function execute(User $user, int $shopId): bool
    {
        $isFollowing = $user->follow_shops()->where('shops.id', $shopId)->exists();

        if ($isFollowing) {
            $user->follow_shops()->detach($shopId);

            return false;
        }

        $user->follow_shops()->attach($shopId);

        return true;
    }
}
