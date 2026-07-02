<?php

declare(strict_types=1);

namespace App\Modules\Attribute\Services;

use App\Models\Attribute;
use App\Modules\Attribute\Actions\CreateAttributeAction;
use App\Modules\Attribute\Actions\UpdateAttributeAction;
use App\Modules\Attribute\DTO\AttributeData;

final class AttributeWriteService
{
    public function __construct(
        private readonly CreateAttributeAction $createAttributeAction,
        private readonly UpdateAttributeAction $updateAttributeAction,
    ) {}

    public function createAttribute(AttributeData $data): Attribute
    {
        return $this->createAttributeAction->execute($data);
    }

    public function updateAttribute(Attribute $attribute, AttributeData $data): Attribute
    {
        return $this->updateAttributeAction->execute($attribute, $data);
    }
}
