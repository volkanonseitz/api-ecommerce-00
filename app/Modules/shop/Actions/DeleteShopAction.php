<?php

declare(strict_types=1);

namespace App\Modules\Shop\Actions;

use App\Models\Shop;

final class DeleteShopAction
{
    public function execute(Shop $shop): void
    {
        $shop->delete();
    }
}
