<?php

namespace App\Modules\Wishlist\Http\Controllers;

use App\Http\Controllers\BaseController;
use App\Models\Product;
use App\Modules\Wishlist\Actions\AddProductToWishlistAction;
use App\Modules\Wishlist\Actions\RemoveProductFromWishlistAction;
use App\Modules\Wishlist\Actions\ToggleProductWishlistAction;
use App\Modules\Wishlist\DTO\WishlistData;
use App\Modules\Wishlist\Http\Requests\WishlistCreateRequest;
use App\Modules\Wishlist\Http\Resources\WishlistResource;
use App\Modules\Wishlist\Services\WishlistQueryService;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

class WishlistController extends BaseController
{
    public function __construct(
        private readonly WishlistQueryService $queryService,
        private readonly AddProductToWishlistAction $addWishlistAction,
        private readonly RemoveProductFromWishlistAction $removeWishlistAction,
        private readonly ToggleProductWishlistAction $toggleWishlistAction,
    ) {}

    /**
     * GET /wishlist
     * Menampilkan semua product dalam wishlist user yang sedang login
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $this->authorize('viewAny', Product::class); // Assuming policy for products in wishlist

        $limit = $request->limit ?? 15;
        $products = $this->queryService->getUserWishlistProducts($user, $limit);

        return $this->sendPaginated(
            $products,
            WishlistResource::collection($products->getCollection()),
            'Daftar produk dalam wishlist berhasil diambil.'
        );
    }

    /**
     * POST /wishlist (store)
     * Menambahkan product ke wishlist (menggunakan storeWishlist)
     */
    public function store(WishlistCreateRequest $request)
    {
        $user = $request->user();
        $product = Product::findOrFail($request->product_id);
        $this->authorize('add', $product);

        $data = WishlistData::fromRequest($request->validated(), $user->id);
        $wishlist = $this->addWishlistAction->execute($data);
        if (! $wishlist) {
            throw new HttpException(400, config('notice.ALREADY_ADDED_TO_WISHLIST_FOR_THIS_PRODUCT'));
        }

        return $this->sendSuccess(new WishlistResource($wishlist), 'Produk berhasil ditambahkan ke wishlist.');
    }

    /**
     * POST /wishlist/toggle
     * Toggle wishlist (tambah jika belum, hapus jika sudah)
     */
    public function toggle(WishlistCreateRequest $request)
    {
        $user = $request->user();
        $product = Product::findOrFail($request->product_id); // Find product to pass to policy
        $this->authorize('toggle', $product);

        $data = WishlistData::fromRequest($request->validated(), $user->id);
        $result = $this->toggleWishlistAction->execute($data);

        return $this->sendSuccess($result, 'Wishlist berhasil diubah.');
    }

    /**
     * DELETE /wishlist/{id}
     * Hapus wishlist berdasarkan product_id
     */
    public function destroy(Request $request, $id)
    {
        $user = $request->user();
        $product = Product::findOrFail((int) $id); // Parameter $id is product_id
        $this->authorize('remove', $product);

        $deleted = $this->removeWishlistAction->execute($user, $product->id);
        if (! $deleted) {
            throw new HttpException(404, config('notice.NOT_FOUND'));
        }

        return $this->sendSuccess(null, 'Produk berhasil dihapus dari wishlist.');
    }

    /**
     * GET /wishlist/in-wishlist/{product_id}
     * Cek apakah product sudah ada di wishlist user
     */
    public function in_wishlist(Request $request, int $product_id)
    {
        $user = $request->user();
        // No authorization needed for guest users, they just get false.
        if (! $user) {
            return response()->json(false);
        }
        $this->authorize('checkStatus', Product::class); // Authorize that a user can check wishlist status

        $result = $this->queryService->isInWishlist($user, $product_id);

        return response()->json($result);
    }
}
