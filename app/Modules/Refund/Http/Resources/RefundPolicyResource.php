<?php

declare(strict_types=1);

namespace App\Modules\Refund\Http\Resources;

use App\Modules\Shop\Http\Resources\ShopResource;
use Illuminate\Http\Resources\Json\JsonResource;

class RefundPolicyResource extends JsonResource
{
    public function toArray($request): array
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
            'shop' => $this->whenLoaded('shop', fn () => new ShopResource($this->resource->shop)),
            'refunds' => $this->whenLoaded('refunds', fn () => RefundResource::collection($this->resource->refunds)),
            'created_at' => $this->resource->created_at,
            'updated_at' => $this->resource->updated_at,
            'deleted_at' => $this->resource->deleted_at,
        ];
    }
}
