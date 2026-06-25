<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class RelatedProductResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->resource->id,
            'name' => $this->resource->name,
            'slug' => $this->resource->slug,
            'language' => $this->resource->language,
            'translated_languages' => $this->resource->translated_languages,
            'product_type' => $this->resource->product_type,
            'sale_price' => $this->resource->sale_price,
            'max_price' => $this->resource->max_price,
            'min_price' => $this->resource->min_price,
            'image' => $this->resource->image,
            'video' => $this->resource->video,
            'price' => $this->resource->price,
            'unit' => $this->resource->unit,
        ];
    }
}
