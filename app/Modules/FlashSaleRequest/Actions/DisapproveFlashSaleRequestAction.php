<?php

declare(strict_types=1);

namespace App\Modules\FlashSaleRequest\Actions;

use App\Models\FlashSale;
use App\Models\FlashSaleRequest;
use App\Modules\FlashSale\Events\FlashSaleProcessed;

final class DisapproveFlashSaleRequestAction
{
    public function execute(int $id): void
    {
        $request = FlashSaleRequest::with(['products', 'flashSale'])->findOrFail($id);
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
}
