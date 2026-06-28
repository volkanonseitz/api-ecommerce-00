<?php

declare(strict_types=1);

namespace App\Modules\Analytics\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin array{ category_id: int, category_name: string, shop_name?: string, product_count?: int, total_sales?: float }
 */
class CategoryWiseResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'category_id' => $this['category_id'],
            'category_name' => $this['category_name'],
            'shop_name' => $this['shop_name'] ?? null,
            'product_count' => $this['product_count'] ?? null,
            'total_sales' => $this['total_sales'] ?? null,
        ];
    }
}
