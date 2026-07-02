<?php

declare(strict_types=1);

namespace App\Modules\AttributeValue\Services;

use App\Models\AttributeValue;
use Illuminate\Database\Eloquent\Collection;

final class AttributeValueQueryService
{
    /**
     * @return Collection<int, AttributeValue>
     */
    public function getAllAttributeValues(): Collection
    {
        return AttributeValue::with('attribute')->get();
    }

    public function getAttributeValueById(int $id): AttributeValue
    {
        return AttributeValue::with('attribute')->findOrFail($id);
    }

    public function findOrFail(int $id): AttributeValue
    {
        return AttributeValue::findOrFail($id);
    }
}
