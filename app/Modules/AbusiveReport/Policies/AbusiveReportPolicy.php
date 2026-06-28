<?php

declare(strict_types=1);

namespace App\Modules\AbusiveReport\Policies;

use App\Models\AbusiveReport;
use App\Models\User;

class AbusiveReportPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('manage-abusive-reports');
    }

    public function view(User $user, AbusiveReport $report): bool
    {
        return $user->hasPermissionTo('manage-abusive-reports')
            || $user->id === $report->user_id;
    }

    public function create(User $user): bool
    {
        // Siapa pun yang login boleh membuat laporan
        return true;
    }

    public function delete(User $user, AbusiveReport $report): bool
    {
        return $user->hasPermissionTo('manage-abusive-reports')
            || $user->id === $report->user_id;
    }

    public function accept(User $user): bool
    {
        return $user->hasPermissionTo('manage-abusive-reports');
    }

    public function reject(User $user): bool
    {
        return $user->hasPermissionTo('manage-abusive-reports');
    }
}
