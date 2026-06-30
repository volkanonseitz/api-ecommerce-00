<?php

declare(strict_types=1);

namespace App\Modules\FlashSale\Actions;

use App\Models\FlashSale;
use App\Models\Product;
use App\Modules\FlashSale\DTO\FlashSaleData;

class UpdateFlashSaleAction
{
    public function execute(FlashSale $flashSale, FlashSaleData $data): FlashSale
    {
        $oldProductIds = $flashSale->sale_builder['product_ids'] ?? [];
        $newProductIds = $data->sale_builder['product_ids'] ?? [];

        if (! empty($newProductIds)) {
            $flashSale->products()->sync($newProductIds);
            $this->setProductInFlashSale($newProductIds);

            $removedIds = array_diff($oldProductIds, $newProductIds);
            if (! empty($removedIds)) {
                $this->unsetProductFromFlashSale($removedIds);
            }
        }

        $flashSale->update($data->toArray());

        return $flashSale->fresh();
    }

    private function setProductInFlashSale(array $productIds): void
    {
        Product::whereIn('id', $productIds)->update(['in_flash_sale' => true]);
    }

    private function unsetProductFromFlashSale(array $productIds): void
    {
        Product::whereIn('id', $productIds)->update(['in_flash_sale' => false]);
    }
}
