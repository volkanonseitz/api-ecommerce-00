<?php

declare(strict_types=1);

namespace App\Modules\Withdraw\Actions;

use App\Models\Withdraw;
use Illuminate\Contracts\Auth\Authenticatable;

final class DeleteWithdrawAction
{
    public function execute(Withdraw $withdraw, Authenticatable $user): void
    {
        // Policy handles authorization for super admin
        $withdraw->delete();
    }
}
