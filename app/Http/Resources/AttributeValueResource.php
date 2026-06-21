<?php

namespace App\Http\Resources;

use App\Models\AttributeValue;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin AttributeValue
 */
class AttributeValueResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'value' => $this->value,
            'attribute_id' => $this->attribute_id,
            'slug' => $this->slug,
            'meta' => $this->meta,
            'language' => $this->language,
            'translated_languages' => $this->translated_languages,
        ];
    }
}
