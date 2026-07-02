<?php

declare(strict_types=1);

namespace App\Modules\Withdraw\Actions;

use App\Models\Withdraw;
use Illuminate\Contracts\Auth\Authenticatable;

final class ApproveWithdrawAction
{
    public function execute(Withdraw $withdraw, string $status, Authenticatable $user): Withdraw
    {
        // Policy handles authorization for super admin
        $withdraw->status = $status;
        $withdraw->save();

        return $withdraw->fresh();
    }
}
