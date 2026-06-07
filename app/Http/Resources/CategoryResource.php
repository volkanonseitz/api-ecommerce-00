<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CategoryResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'language' => $this->language,
            'parent' => $this->parentCategory ? new CategoryResource($this->parentCategory) : null,
            'children' => CategoryResource::collection($this->whenLoaded('children')),
            'products_count' => $this->products_count,
            'details' => $this->details,
            'image' => $this->image,
            'icon' => $this->icon,
            'type_id' => $this->type_id,
            'banner_image' => $this->banner_image,
            'type' => $this->whenLoaded('type', fn() => ['id' => $this->type->id, 'name' => $this->type->name]),
        ];
    }
}