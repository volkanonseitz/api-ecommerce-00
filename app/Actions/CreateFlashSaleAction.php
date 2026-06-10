<?php

namespace App\Actions;

use App\Models\FlashSale;
use App\Models\Product;
use App\DTO\FlashSaleData;

class CreateFlashSaleAction
{
    public function execute(FlashSaleData $data): FlashSale
    {
        $flashSale = FlashSale::create($data->toArray());
        if (!empty($data->sale_builder['product_ids'])) {
            $flashSale->products()->attach($data->sale_builder['product_ids']);
            $this->setProductInFlashSale($data->sale_builder['product_ids']);
        }
        return $flashSale;
    }

    protected function setProductInFlashSale(array $productIds): void
    {
        Product::whereIn('id', $productIds)->update(['in_flash_sale' => true]);
    }
}