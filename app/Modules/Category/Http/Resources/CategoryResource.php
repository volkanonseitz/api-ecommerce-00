<?php

declare(strict_types=1);

namespace App\Modules\Category\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CategoryResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->resource->id,
            'name' => $this->resource->name,
            'slug' => $this->resource->slug,
            'language' => $this->resource->language,
            'parent' => $this->resource->parentCategory
                ? new CategoryResource($this->resource->parentCategory)
                : null,
            'children' => CategoryResource::collection($this->whenLoaded('children')),
            'products_count' => $this->resource->products_count,
            'details' => $this->resource->details,
            'image' => $this->resource->image,
            'icon' => $this->resource->icon,
            'type_id' => $this->resource->type_id,
            'banner_image' => $this->resource->banner_image,
            'type' => $this->whenLoaded(
                'type',
                fn () => [
                    'id' => $this->resource->type->id,
                    'name' => $this->resource->type->name,
                ]
            ),
        ];
    }
}
