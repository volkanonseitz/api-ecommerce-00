<?php

declare(strict_types=1);

namespace App\Modules\Shop\Actions;

use App\Events\ShopMaintenance;
use App\Models\Product;
use App\Models\Shop;

final class EnableShopMaintenanceAction
{
    public function execute(Shop $shop): void
    {
        Product::where('shop_id', $shop->id)->update(['visibility' => 'private']);
        event(new ShopMaintenance($shop, 'start'));
    }
}
