<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'type' => $this->type ? ['id' => $this->type->id, 'name' => $this->type->name] : null,
            'language' => $this->language,
            'translated_languages' => $this->translated_languages,
            'product_type' => $this->product_type,
            'shop' => $this->shop ? ['id' => $this->shop->id, 'name' => $this->shop->name] : null,
            'sale_price' => $this->sale_price,
            'max_price' => $this->max_price,
            'min_price' => $this->min_price,
            'image' => $this->image,
            'status' => $this->status,
            'price' => $this->price,
            'quantity' => $this->quantity,
            'unit' => $this->unit,
            'sku' => $this->sku,
            'sold_quantity' => $this->sold_quantity,
            'in_flash_sale' => $this->in_flash_sale,
            'visibility' => $this->visibility,
        ];
    }
}