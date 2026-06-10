<?php

namespace App\Services;

use App\Models\FlashSaleRequest;
use App\Models\FlashSale;
use App\DTO\FlashSaleRequestData;
use App\Actions\CreateFlashSaleRequestAction;
use App\Actions\UpdateFlashSaleRequestAction;
use App\Events\FlashSaleProcessed;
use App\Enums\Permission;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class FlashSaleVendorRequestService
{
    public function __construct(
        private CreateFlashSaleRequestAction $createAction,
        private UpdateFlashSaleRequestAction $updateAction,
    ) {}

    public function getRequestsQuery(Request $request): \Illuminate\Database\Eloquent\Builder
    {
        $language = $request->language ?? config('constants.DEFAULT_LANGUAGE', 'en');
        return FlashSaleRequest::where('language', $language);
    }

    public function findOrFail(int $id): FlashSaleRequest
    {
        return FlashSaleRequest::findOrFail($id);
    }

    public function create(FlashSaleRequestData $data): FlashSaleRequest
    {
        return $this->createAction->execute($data);
    }

    public function update(FlashSaleRequest $flashSaleRequest, FlashSaleRequestData $data): FlashSaleRequest
    {
        return $this->updateAction->execute($flashSaleRequest, $data);
    }

    public function delete(FlashSaleRequest $flashSaleRequest, Authenticatable $user): void
    {
        // Detach products from main flash sale if already attached
        $flashSale = FlashSale::with('products')->find($flashSaleRequest->flash_sale_id);
        $detachedProducts = [];

        if ($flashSale && $flashSaleRequest->products->count()) {
            foreach ($flashSaleRequest->products as $product) {
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
        event(new FlashSaleProcessed('delete_vendor_request', config('constants.DEFAULT_LANGUAGE', 'en'), $eventData));

        $flashSaleRequest->forceDelete();
    }

    public function approveRequest(int $id): void
    {
        $flashSaleRequest = $this->findOrFail($id);
        $flashSaleRequest->request_status = true;
        $flashSale = FlashSale::with('products')->find($flashSaleRequest->flash_sale_id);
        $attachedProducts = [];

        foreach ($flashSaleRequest->products as $product) {
            if ($flashSale && !$flashSale->products->contains($product->id)) {
                $flashSale->products()->attach($flashSale->id, ['product_id' => $product->id]);
                $attachedProducts[] = $product->id;
            }
        }
        $flashSaleRequest->save();

        $eventData = [
            'attached_product_ids' => $attachedProducts,
            'requested_flash_sale' => $flashSale,
        ];
        event(new FlashSaleProcessed('append_attached_products', config('constants.DEFAULT_LANGUAGE', 'en'), $eventData));
    }

    public function disapproveRequest(int $id): void
    {
        $flashSaleRequest = $this->findOrFail($id);
        $flashSaleRequest->request_status = false;
        $flashSale = FlashSale::with('products')->find($flashSaleRequest->flash_sale_id);
        $detachedProducts = [];

        foreach ($flashSaleRequest->products as $product) {
            if ($flashSale && $flashSale->products->contains($product->id)) {
                $flashSale->products()->detach($product->id);
                $detachedProducts[] = $product->id;
            }
        }
        if ($flashSale) $flashSale->save();
        $flashSaleRequest->save();

        $eventData = [
            'detached_product_ids' => $detachedProducts,
            'requested_flash_sale' => $flashSale,
        ];
        event(new FlashSaleProcessed('remove_attached_products', config('constants.DEFAULT_LANGUAGE', 'en'), $eventData));
    }

    public function getRequestedProductsQuery(Request $request, int $vendorRequestId)
    {
        $language = $request->language ?? config('constants.DEFAULT_LANGUAGE', 'en');
        $productIds = FlashSaleRequest::where('id', $vendorRequestId)
            ->where('language', $language)
            ->join('flash_sale_requests_products', 'flash_sale_requests.id', '=', 'flash_sale_requests_products.flash_sale_requests_id')
            ->join('products', 'flash_sale_requests_products.product_id', '=', 'products.id')
            ->select('products.id')
            ->pluck('id');
        return Product::whereIn('id', $productIds);
    }

    public function hasPermission(?Authenticatable $user): bool
    {
        if (!$user) return false;
        // Untuk store dan update, bisa siapa saja (vendor). Untuk delete/approve/disapprove hanya super admin atau store owner/staff? Sesuai asli:
        // di destroy: super_admin atau store_owner atau staff
        // approve/disapprove: super_admin
        return $user->hasPermissionTo(Permission::SUPER_ADMIN->value)
            || $user->hasPermissionTo(Permission::STORE_OWNER->value)
            || $user->hasPermissionTo(Permission::STAFF->value);
    }
}