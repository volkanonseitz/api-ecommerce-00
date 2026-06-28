<?php

namespace App\Http\Resources;

use App\Modules\Product\Http\Resources\ProductResource;
use Illuminate\Http\Resources\Json\JsonResource;

class FlashSaleResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->resource->id,
            'title' => $this->resource->title,
            'slug' => $this->resource->slug,
            'description' => $this->resource->description,
            'start_date' => $this->resource->start_date?->toISOString(),
            'end_date' => $this->resource->end_date?->toISOString(),
            'sale_status' => $this->resource->sale_status,
            'type' => $this->resource->type,
            'rate' => $this->resource->rate,
            'sale_builder' => $this->resource->sale_builder,
            'image' => $this->resource->image,
            'cover_image' => $this->resource->cover_image,
            'language' => $this->resource->language,
            'deleted_at' => $this->resource->deleted_at,
            'created_at' => $this->resource->created_at,
            'updated_at' => $this->resource->updated_at,
            'products' => ProductResource::collection($this->whenLoaded('products')),
        ];
    }
}
