<?php

namespace App\Http\Resources;

use App\Models\TermsAndConditions;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin TermsAndConditions
 */
class TermsConditionResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->resource->id,
            'title' => $this->resource->title,
            'slug' => $this->resource->slug,
            'description' => $this->resource->description,
            'type' => $this->resource->type,
            'issued_by' => $this->resource->issued_by,
            'is_approved' => $this->resource->is_approved,
            'language' => $this->resource->language,
            'translated_languages' => $this->resource->translated_languages,
        ];
    }
}
