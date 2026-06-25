<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class AttributeResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->resource->id,
            'name' => $this->resource->name,
            'shop_id' => $this->resource->shop_id,
            'language' => $this->resource->language,
            'translated_languages' => $this->resource->translated_languages,
            'slug' => $this->resource->slug,
            'values' => AttributeValueResource::collection($this->whenLoaded('values')),
        ];
    }
}
