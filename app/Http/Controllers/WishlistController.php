<?php

namespace App\Http\Controllers;

use App\Services\WishlistService;
use App\Http\Requests\WishlistCreateRequest;
use App\DTO\WishlistData;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Auth\Access\AuthorizationException;
use Symfony\Component\HttpKernel\Exception\HttpException;

class WishlistController extends Controller
{
    public function __construct(private WishlistService $wishlistService) {}

    /**
     * GET /wishlist
     * Menampilkan semua product dalam wishlist user yang sedang login
     */
    public function index(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            throw new AuthorizationException(config('notice.NOT_AUTHORIZED'));
        }
        $limit = $request->limit ?? 15;
        $products = $this->wishlistService->getUserWishlistProducts($user, $limit);
        return response()->json($products);
    }

    /**
     * POST /wishlist (store)
     * Menambahkan product ke wishlist (menggunakan storeWishlist)
     */
    public function store(WishlistCreateRequest $request)
    {
        $user = $request->user();
        if (!$user) {
            throw new AuthorizationException(config('notice.NOT_AUTHORIZED'));
        }
        $data = WishlistData::fromRequest($request->validated(), $user->id);
        $wishlist = $this->wishlistService->addToWishlist($data);
        if (!$wishlist) {
            throw new HttpException(400, config('notice.ALREADY_ADDED_TO_WISHLIST_FOR_THIS_PRODUCT'));
        }
        return response()->json($wishlist);
    }

    /**
     * POST /wishlist/toggle
     * Toggle wishlist (tambah jika belum, hapus jika sudah)
     */
    public function toggle(WishlistCreateRequest $request)
    {
        $user = $request->user();
        if (!$user) {
            throw new AuthorizationException(config('notice.NOT_AUTHORIZED'));
        }
        $data = WishlistData::fromRequest($request->validated(), $user->id);
        $result = $this->wishlistService->toggleWishlist($data);
        return response()->json($result);
    }

    /**
     * DELETE /wishlist/{id}
     * Hapus wishlist berdasarkan id (bisa id wishlist, kode asli menghapus berdasarkan product_id)
     * Namun di kode asli, destroy menerima $id dan mencari product berdasarkan id, lalu menghapus wishlist.
     * Jadi parameter $id adalah product_id, bukan wishlist id.
     */
    public function destroy(Request $request, $id)
    {
        $user = $request->user();
        if (!$user) {
            throw new AuthorizationException(config('notice.NOT_AUTHORIZED'));
        }
        $product = Product::find($id);
        if (!$product) {
            throw new HttpException(404, config('notice.NOT_FOUND'));
        }
        $deleted = $this->wishlistService->removeFromWishlist($user, $product->id);
        if (!$deleted) {
            throw new HttpException(404, config('notice.NOT_FOUND'));
        }
        return response()->json(['message' => 'Wishlist item deleted']);
    }

    /**
     * GET /wishlist/in-wishlist/{product_id}
     * Cek apakah product sudah ada di wishlist user
     */
    public function in_wishlist(Request $request, $product_id)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(false);
        }
        $result = $this->wishlistService->isInWishlist($user, (int)$product_id);
        return response()->json($result);
    }
}