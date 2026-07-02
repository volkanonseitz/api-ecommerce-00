<?php

declare(strict_types=1);

namespace App\Modules\AttributeValue\Services;

use App\Models\AttributeValue;
use App\Modules\AttributeValue\Actions\CreateAttributeValueAction;
use App\Modules\AttributeValue\Actions\UpdateAttributeValueAction;
use App\Modules\AttributeValue\DTO\AttributeValueData;

final class AttributeValueWriteService
{
    public function __construct(
        private readonly CreateAttributeValueAction $createAction,
        private readonly UpdateAttributeValueAction $updateAction,
    ) {}

    public function createAttributeValue(AttributeValueData $data): AttributeValue
    {
        return $this->createAction->execute($data);
    }

    public function updateAttributeValue(AttributeValue $attributeValue, AttributeValueData $data): AttributeValue
    {
        return $this->updateAction->execute($attributeValue, $data);
    }
}
