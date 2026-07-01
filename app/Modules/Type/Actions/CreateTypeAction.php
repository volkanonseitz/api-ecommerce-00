<?php

declare(strict_types=1);

namespace App\Modules\Type\Actions;

use App\Models\Type;
use App\Modules\Type\DTO\TypeData;

class CreateTypeAction
{
    public function execute(TypeData $data): Type
    {
        return Type::create($data->toArray());
    }
}