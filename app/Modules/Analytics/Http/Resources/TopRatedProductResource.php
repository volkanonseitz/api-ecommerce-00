<?php

declare(strict_types=1);

namespace App\Modules\Analytics\Http\Resources;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Product
 *
 * @property-read float|null $average_rating
 * @property-read int $reviews_count
 */
class TopRatedProductResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'average_rating' => (float) ($this->average_rating ?? 0),
            'reviews_count' => $this->reviews_count ?? 0,
            'shop' => [
                'id' => $this->shop->id,
                'name' => $this->shop->name,
            ],
            'type' => [
                'id' => $this->type->id,
                'name' => $this->type->name,
            ],
        ];
    }
}
