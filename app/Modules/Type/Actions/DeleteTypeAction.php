<?php

declare(strict_types=1);

namespace App\Modules\Type\Actions;

use App\Models\Type;
use Illuminate\Support\Facades\DB;

class DeleteTypeAction
{
    public function execute(Type $type): void
    {
        DB::transaction(function () use ($type) {
            $type->banners()->delete();
            $type->delete();
        });
    }
}