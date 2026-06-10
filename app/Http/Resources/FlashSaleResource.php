<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class FlashSaleResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'description' => $this->description,
            'start_date' => $this->start_date?->toISOString(),
            'end_date' => $this->end_date?->toISOString(),
            'sale_status' => $this->sale_status,
            'type' => $this->type,
            'rate' => $this->rate,
            'sale_builder' => $this->sale_builder,
            'image' => $this->image,
            'cover_image' => $this->cover_image,
            'language' => $this->language,
            'deleted_at' => $this->deleted_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'products' => ProductResource::collection($this->whenLoaded('products')),
        ];
    }
}