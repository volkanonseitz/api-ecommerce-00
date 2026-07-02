<?php

namespace App\Modules\RefundReason\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class RefundReasonResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->resource->id,
            'name' => $this->resource->name,
            'slug' => $this->resource->slug,
            'language' => $this->resource->language,
            'translated_languages' => $this->resource->translated_languages,
            'created_at' => $this->resource->created_at,
            'updated_at' => $this->resource->updated_at,
        ];
    }
}
