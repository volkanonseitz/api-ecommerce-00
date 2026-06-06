<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class AttributeResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'shop_id' => $this->shop_id,
            'language' => $this->language,
            'translated_languages' => $this->translated_languages,
            'slug' => $this->slug,
            'values' => AttributeValueResource::collection($this->whenLoaded('values')),
        ];
    }
}