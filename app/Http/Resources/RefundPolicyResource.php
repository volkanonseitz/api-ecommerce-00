<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class RefundPolicyResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->resource->id,
            'title' => $this->resource->title,
            'slug' => $this->resource->slug,
            'target' => $this->resource->target,
            'status' => $this->resource->status,
            'description' => $this->resource->description,
            'language' => $this->resource->language,
            'translated_languages' => $this->resource->translated_languages,
            'shop' => $this->resource->whenLoaded('shop', fn () => new ShopResource($this->resource->shop)),
            'refunds' => $this->resource->whenLoaded('refunds', fn () => RefundResource::collection($this->resource->refunds)),
            'created_at' => $this->resource->created_at,
            'updated_at' => $this->resource->updated_at,
            'deleted_at' => $this->resource->deleted_at,
        ];
    }
}
