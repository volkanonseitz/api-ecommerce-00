<?php

declare(strict_types=1);

namespace App\Modules\FlashSaleRequest\Services;

use App\Enums\Permission;
use App\Events\FlashSaleProcessed;
use App\Models\FlashSale;
use App\Models\FlashSaleRequest;
use App\Models\Product;
use App\Modules\FlashSaleRequest\Actions\CreateFlashSaleRequestAction;
use App\Modules\FlashSaleRequest\Actions\UpdateFlashSaleRequestAction;
use App\Modules\FlashSaleRequest\DTO\FlashSaleRequestData;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class FlashSaleRequestService
{
    public function __construct(
        private CreateFlashSaleRequestAction $createAction,
        private UpdateFlashSaleRequestAction $updateAction,
    ) {}

    public function hasPermission(?Authenticatable $user): bool
    {
        if (! $user) {
            return false;
        }

        return $user->hasPermissionTo(Permission::SUPER_ADMIN->value)
            || $user->hasPermissionTo(Permission::STORE_OWNER->value)
            || $user->hasPermissionTo(Permission::STAFF->value);
    }

    public function getRequestsQuery(Request $request): Builder
    {
        $language = $request->language ?? config('shop.default_language', 'id');

        return FlashSaleRequest::where('language', $language)->with(['products', 'flashSale']);
    }

    public function findOrFail(int $id): FlashSaleRequest
    {
        return FlashSaleRequest::with(['products', 'flashSale'])->findOrFail($id);
    }

    public function create(FlashSaleRequestData $data): FlashSaleRequest
    {
        return $this->createAction->execute($data);
    }

    public function update(FlashSaleRequest $request, FlashSaleRequestData $data): FlashSaleRequest
    {
        return $this->updateAction->execute($request, $data);
    }

    public function delete(FlashSaleRequest $request, Authenticatable $user): void
    {
        // Detach products from main flash sale if already attached
        $flashSale = FlashSale::with('products')->find($request->flash_sale_id);
        $detachedProducts = [];

        if ($flashSale && $request->products->count()) {
            foreach ($request->products as $product) {
                if ($flashSale->products->contains($product->id)) {
                    $flashSale->products()->detach($product->id);
                    $detachedProducts[] = $product->id;
                }
            }
            $flashSale->save();
        }

        $eventData = [
            'requested_flash_sale' => $flashSale,
            'detached_products' => $detachedProducts,
        ];
        event(new FlashSaleProcessed('delete_vendor_request', config('shop.default_language', 'id'), $eventData));

        $request->forceDelete();
    }

    public function approveRequest(int $id): void
    {
        $request = $this->findOrFail($id);
        $request->request_status = true;

        $flashSale = FlashSale::with('products')->find($request->flash_sale_id);
        $attachedProducts = [];

        foreach ($request->products as $product) {
            if ($flashSale && ! $flashSale->products->contains($product->id)) {
                $flashSale->products()->attach($flashSale->id, ['product_id' => $product->id]);
                $attachedProducts[] = $product->id;
            }
        }
        $request->save();

        $eventData = [
            'attached_product_ids' => $attachedProducts,
            'requested_flash_sale' => $flashSale,
        ];
        event(new FlashSaleProcessed('append_attached_products', config('shop.default_language', 'id'), $eventData));
    }

    public function disapproveRequest(int $id): void
    {
        $request = $this->findOrFail($id);
        $request->request_status = false;

        $flashSale = FlashSale::with('products')->find($request->flash_sale_id);
        $detachedProducts = [];

        foreach ($request->products as $product) {
            if ($flashSale && $flashSale->products->contains($product->id)) {
                $flashSale->products()->detach($product->id);
                $detachedProducts[] = $product->id;
            }
        }
        if ($flashSale) {
            $flashSale->save();
        }
        $request->save();

        $eventData = [
            'detached_product_ids' => $detachedProducts,
            'requested_flash_sale' => $flashSale,
        ];
        event(new FlashSaleProcessed('remove_attached_products', config('shop.default_language', 'id'), $eventData));
    }

    public function getRequestedProductsQuery(Request $request, int $vendorRequestId): Builder
    {
        $language = $request->language ?? config('shop.default_language', 'id');
        $productIds = FlashSaleRequest::where('id', $vendorRequestId)
            ->where('language', $language)
            ->join('flash_sale_requests_products', 'flash_sale_requests.id', '=', 'flash_sale_requests_products.flash_sale_requests_id')
            ->join('products', 'flash_sale_requests_products.product_id', '=', 'products.id')
            ->select('products.id')
            ->pluck('id');

        return Product::whereIn('id', $productIds);
    }
}
