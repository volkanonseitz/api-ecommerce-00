<?php

declare(strict_types=1);

namespace App\Modules\Wishlist\Actions;

use App\Models\Wishlist;
use Illuminate\Contracts\Auth\Authenticatable;

final class RemoveProductFromWishlistAction
{
    /**
     * @return bool true if successfully deleted, false otherwise
     */
    public function execute(Authenticatable $user, int $productId): bool
    {
        $wishlist = Wishlist::where('user_id', $user->id)
            ->where('product_id', $productId)
            ->first();
        if ($wishlist) {
            return $wishlist->delete();
        }

        return false;
    }
}
