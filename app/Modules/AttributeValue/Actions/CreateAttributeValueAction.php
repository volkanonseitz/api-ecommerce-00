<?php

declare(strict_types=1);

namespace App\Modules\Attribute\Actions;

use App\Models\AttributeValue;
use App\Modules\Attribute\DTO\AttributeValueData;

class CreateAttributeValueAction
{
    public function execute(AttributeValueData $data): AttributeValue
    {
        $attributes = array_filter([
            'value' => $data->value,
            'meta' => $data->meta,
            'price' => $data->price,
            'shop_id' => $data->shop_id,
            'attribute_id' => $data->attribute_id,
            'language' => $data->language,
        ], fn ($v) => ! is_null($v));

        return AttributeValue::create($attributes);
    }
}
