<?php

declare(strict_types=1);

namespace App\Modules\Shop\Actions;

use App\Models\User;

final class RemoveStaffAction
{
    public function execute(User $staff): void
    {
        $staff->delete();
    }
}
