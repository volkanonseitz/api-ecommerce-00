<?php

namespace App\Services;

use App\Models\FlashSale;
use App\Models\Product;
use App\DTO\FlashSaleData;
use App\Actions\CreateFlashSaleAction;
use App\Actions\UpdateFlashSaleAction;
use App\Events\FlashSaleProcessed;
use App\Enums\Permission;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\Request;

class FlashSaleService
{
    public function __construct(
        private CreateFlashSaleAction $createFlashSale,
        private UpdateFlashSaleAction $updateFlashSale,
    ) {}

    public function hasPermission(?Authenticatable $user): bool
    {
        if (!$user) return false;
        return $user->hasPermissionTo(Permission::SUPER_ADMIN->value);
    }

    public function getFlashSalesQuery(Request $request)
    {
        $language = $request->language ?? config('shop.default_language', 'id');
        $query = FlashSale::where('language', $language);

        if ($request->request_from === 'vendor') {
            $query->whereDate('start_date', '>', now()->toDateString());
        }

        return $query;
    }

    public function findFlashSaleBySlug(string $slug, string $language): ?FlashSale
    {
        return FlashSale::where('slug', $slug)->where('language', $language)->first();
    }

    public function createFlashSale(FlashSaleData $data): FlashSale
    {
        return $this->createFlashSale->execute($data);
    }

    public function updateFlashSale(FlashSale $flashSale, FlashSaleData $data): FlashSale
    {
        return $this->updateFlashSale->execute($flashSale, $data);
    }

    public function deleteFlashSale(FlashSale $flashSale): void
    {
        $flashSale->delete();
    }

    public function getProductsByFlashSaleSlug(string $slug, string $language, int $perPage = 10)
    {
        $productIds = FlashSale::where('slug', $slug)
            ->where('language', $language)
            ->join('flash_sale_products', 'flash_sales.id', '=', 'flash_sale_products.flash_sale_id')
            ->join('products', 'flash_sale_products.product_id', '=', 'products.id')
            ->select('products.id')
            ->pluck('id');

        return Product::whereIn('id', $productIds)->paginate($perPage);
    }

    public function getFlashSaleInfoByProductId(int $productId): array
    {
        $product = Product::find($productId);
        return $product ? $product->flash_sales->toArray() : [];
    }
}