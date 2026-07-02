<?php

declare(strict_types=1);

namespace App\Modules\AttributeValue\Actions;

use App\Models\AttributeValue;

final class DeleteAttributeValueAction
{
    public function execute(AttributeValue $attributeValue): void
    {
        $attributeValue->delete();
    }
}
