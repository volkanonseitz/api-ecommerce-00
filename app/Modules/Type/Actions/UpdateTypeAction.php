<?php

declare(strict_types=1);

namespace App\Modules\Type\Actions;

use App\Models\Type;
use App\Modules\Type\DTO\TypeData;

class UpdateTypeAction
{
    public function execute(Type $type, TypeData $data): Type
    {
        $type->update($data->toArray());
        return $type->fresh();
    }
}