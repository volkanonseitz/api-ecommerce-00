<?php

declare(strict_types=1);

namespace App\Modules\AttributeValue\Http\Resources;

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
            'id' => $this->resource->id,
            'value' => $this->resource->value,
            'attribute_id' => $this->resource->attribute_id,
            'slug' => $this->resource->slug,
            'meta' => $this->resource->meta,
            'language' => $this->resource->language,
            'translated_languages' => $this->resource->translated_languages,
        ];
    }
}
