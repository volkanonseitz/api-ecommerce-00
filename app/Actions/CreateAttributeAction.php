<?php

namespace App\Actions;

use App\Models\Attribute;
use App\DTO\AttributeData;
use Illuminate\Support\Str;

class CreateAttributeAction
{
    public function execute(AttributeData $data): Attribute
    {
        $attributes = array_filter([
            'name' => $data->name,
            'slug' => $data->slug ?? Str::slug($data->name),
            'shop_id' => $data->shop_id,
            'language' => $data->language,
        ], fn($v) => !is_null($v));

        $attribute = Attribute::create($attributes);

        if ($data->values) {
            foreach ($data->values as $valueData) {
                $valueData['attribute_id'] = $attribute->id;
                $attribute->values()->create($valueData);
            }
        }

        return $attribute->load('values');
    }
}