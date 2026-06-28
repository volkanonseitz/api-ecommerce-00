<?php

declare(strict_types=1);

namespace App\Modules\Analytics\Http\Resources;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Product
 *
 * @property-read int $stock
 * @property-read int $low_stock_threshold
 */
class LowStockProductResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'sku' => $this->sku,
            'stock' => $this->stock,
            'low_stock_threshold' => $this->low_stock_threshold,
            'shop' => [
                'id' => $this->shop->id,
                'name' => $this->shop->name,
            ],
            'type' => [
                'id' => $this->type->id,
                'name' => $this->type->name,
            ],
            'price' => $this->price,
        ];
    }
}
