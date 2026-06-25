<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->resource->id,
            'name' => $this->resource->name,
            'slug' => $this->resource->slug,
            'type' => $this->resource->type ? ['id' => $this->resource->type->id, 'name' => $this->resource->type->name] : null,
            'language' => $this->resource->language,
            'translated_languages' => $this->resource->translated_languages,
            'product_type' => $this->resource->product_type,
            'shop' => $this->resource->shop ? ['id' => $this->resource->shop->id, 'name' => $this->resource->shop->name] : null,
            'sale_price' => $this->resource->sale_price,
            'max_price' => $this->resource->max_price,
            'min_price' => $this->resource->min_price,
            'image' => $this->resource->image,
            'status' => $this->resource->status,
            'price' => $this->resource->price,
            'quantity' => $this->resource->quantity,
            'unit' => $this->resource->unit,
            'sku' => $this->resource->sku,
            'sold_quantity' => $this->resource->sold_quantity,
            'in_flash_sale' => $this->resource->in_flash_sale,
            'visibility' => $this->resource->visibility,
        ];
    }
}
