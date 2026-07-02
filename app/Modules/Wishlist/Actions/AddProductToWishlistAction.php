<?php

declare(strict_types=1);

namespace App\Modules\Wishlist\Actions;

use App\Models\Wishlist;
use App\Modules\Wishlist\DTO\WishlistData;

final class AddProductToWishlistAction
{
    public function execute(WishlistData $data): ?Wishlist
    {
        $exists = Wishlist::where('user_id', $data->user_id)
            ->where('product_id', $data->product_id)
            ->exists();
        if ($exists) {
            return null;
        }

        return Wishlist::create($data->toArray());
    }
}
