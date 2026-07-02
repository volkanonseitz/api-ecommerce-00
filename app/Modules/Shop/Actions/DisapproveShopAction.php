<?php

declare(strict_types=1);

namespace App\Modules\Shop\Actions;

use App\Models\Product;
use App\Models\Shop;

final class DisapproveShopAction
{
    public function execute(Shop $shop): Shop
    {
        $shop->forceFill(['is_active' => false])->save();

        Product::where('shop_id', $shop->id)->update(['status' => 'draft']);

        return $shop;
    }
}
