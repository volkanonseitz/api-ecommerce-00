<?php

declare(strict_types=1);

namespace App\Modules\Product\Actions;

use App\Models\Product;

class DeleteProductAction
{
    public function execute(Product $product): void
    {
        $product->delete();
    }
}
