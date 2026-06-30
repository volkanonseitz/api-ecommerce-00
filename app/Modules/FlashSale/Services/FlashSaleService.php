<?php

declare(strict_types=1);

namespace App\Modules\FlashSale\Services;

use App\Enums\Permission;
use App\Models\FlashSale;
use App\Models\Product;
use App\Modules\FlashSale\Actions\CreateFlashSaleAction;
use App\Modules\FlashSale\Actions\UpdateFlashSaleAction;
use App\Modules\FlashSale\DTO\FlashSaleData;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class FlashSaleService
{
    public function __construct(
        private CreateFlashSaleAction $createFlashSale,
        private UpdateFlashSaleAction $updateFlashSale,
    ) {}

    public function hasPermission(?Authenticatable $user): bool
    {
        if (! $user) {
            return false;
        }

        return $user->hasPermissionTo(Permission::SUPER_ADMIN->value);
    }

    public function getFlashSalesQuery(Request $request): Builder
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
        return FlashSale::where('slug', $slug)
            ->where('language', $language)
            ->with('products')
            ->first();
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
        $product = Product::with('flashSales')->find($productId);

        return $product ? $product->flashSales->toArray() : [];
    }
}
