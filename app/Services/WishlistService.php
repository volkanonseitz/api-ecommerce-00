<?php

namespace App\Services;

use App\Models\Wishlist;
use App\Models\Product;
use App\DTO\WishlistData;
use Illuminate\Contracts\Auth\Authenticatable;

class WishlistService
{
    /**
     * Ambil semua produk dalam wishlist user (untuk pagination)
     */
    public function getUserWishlistProducts(Authenticatable $user, int $perPage = 15)
    {
        $productIds = Wishlist::where('user_id', $user->id)->pluck('product_id');
        return Product::whereIn('id', $productIds)->paginate($perPage);
    }

    /**
     * Cek apakah user sudah menambahkan product ke wishlist
     */
    public function isInWishlist(Authenticatable $user, int $productId): bool
    {
        return Wishlist::where('user_id', $user->id)->where('product_id', $productId)->exists();
    }

    /**
     * Tambah product ke wishlist (hanya jika belum ada)
     * @return Wishlist|null
     */
    public function addToWishlist(WishlistData $data): ?Wishlist
    {
        $exists = Wishlist::where('user_id', $data->user_id)
            ->where('product_id', $data->product_id)
            ->exists();
        if ($exists) {
            return null;
        }
        return Wishlist::create($data->toArray());
    }

    /**
     * Hapus product dari wishlist
     * @return bool true jika berhasil dihapus
     */
    public function removeFromWishlist(Authenticatable $user, int $productId): bool
    {
        $wishlist = Wishlist::where('user_id', $user->id)
            ->where('product_id', $productId)
            ->first();
        if ($wishlist) {
            return $wishlist->delete();
        }
        return false;
    }

    /**
     * Hapus wishlist berdasarkan id wishlist (bukan product_id)
     * Digunakan di endpoint destroy dengan parameter id wishlist
     */
    public function deleteWishlistById(Authenticatable $user, int $wishlistId): bool
    {
        $wishlist = Wishlist::where('id', $wishlistId)
            ->where('user_id', $user->id)
            ->first();
        if ($wishlist) {
            return $wishlist->delete();
        }
        return false;
    }

    /**
     * Toggle wishlist: tambah jika belum ada, hapus jika sudah ada
     * @return bool true jika ditambahkan, false jika dihapus
     */
    public function toggleWishlist(WishlistData $data): bool
    {
        $exists = Wishlist::where('user_id', $data->user_id)
            ->where('product_id', $data->product_id)
            ->exists();
        if ($exists) {
            $this->removeFromWishlistById($data->user_id, $data->product_id);
            return false;
        } else {
            Wishlist::create($data->toArray());
            return true;
        }
    }

    /**
     * Hapus wishlist berdasarkan user_id dan product_id (helper)
     */
    protected function removeFromWishlistById(int $userId, int $productId): void
    {
        Wishlist::where('user_id', $userId)->where('product_id', $productId)->delete();
    }
}