<?php

declare(strict_types=1);

namespace App\Modules\FlashSaleRequest\Actions;

use App\Models\FlashSale;
use App\Models\FlashSaleRequest;
use App\Modules\FlashSale\Events\FlashSaleProcessed;

final class ApproveFlashSaleRequestAction
{
    public function execute(int $id): void
    {
        $request = FlashSaleRequest::with(['products', 'flashSale'])->findOrFail($id);
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
}
