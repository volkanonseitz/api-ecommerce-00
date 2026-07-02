<?php

declare(strict_types=1);

namespace App\Modules\Wishlist\Policies;

use App\Models\Product;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class WishlistPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true; // Any authenticated user can view their own wishlist
    }

    /**
     * Determine whether the user can add a product to their wishlist.
     */
    public function add(User $user, Product $product): bool
    {
        return true; // Any authenticated user can add a product to their wishlist
    }

    /**
     * Determine whether the user can toggle a product in their wishlist.
     */
    public function toggle(User $user, Product $product): bool
    {
        return true; // Any authenticated user can toggle a product in their wishlist
    }

    /**
     * Determine whether the user can remove a product from their wishlist.
     */
    public function remove(User $user, Product $product): bool
    {
        return true; // Any authenticated user can remove a product from their wishlist
    }

    /**
     * Determine whether the user can check the wishlist status of a product.
     */
    public function checkStatus(User $user): bool
    {
        return true; // Any authenticated user can check if a product is in their wishlist
    }
}
