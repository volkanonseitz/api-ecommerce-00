<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class RefundPolicyResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'target' => $this->target,
            'status' => $this->status,
            'description' => $this->description,
            'language' => $this->language,
            'translated_languages' => $this->translated_languages,
            'shop' => $this->whenLoaded('shop', fn() => new ShopResource($this->shop)),
            'refunds' => $this->whenLoaded('refunds', fn() => RefundResource::collection($this->refunds)),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,
        ];
    }
}