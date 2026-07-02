<?php

declare(strict_types=1);

namespace App\Modules\FlashSaleRequest\Actions;

use App\Events\FlashSaleProcessed;
use App\Models\FlashSale;
use App\Models\FlashSaleRequest;

final class DeleteFlashSaleRequestAction
{
    public function execute(FlashSaleRequest $request): void
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
}
