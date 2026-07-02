<?php

namespace App\Modules\Resource\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ResourceResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->resource->id,
            'name' => $this->resource->name,
            'slug' => $this->resource->slug,
            'type' => $this->resource->type,
            'price' => $this->resource->price,
            'image' => $this->resource->image,
            'icon' => $this->resource->icon,
            'details' => $this->resource->details,
            'language' => $this->resource->language,
            'translated_languages' => $this->resource->translated_languages,
            'is_approved' => $this->resource->is_approved,
            'created_at' => $this->resource->created_at,
            'updated_at' => $this->resource->updated_at,
        ];
    }
}
