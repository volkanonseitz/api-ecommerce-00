<?php

declare(strict_types=1);

namespace App\Modules\Checkout\Policies;

use App\Models\User;

class CheckoutPolicy
{
    public function verify(User $user): bool
    {
        return $user !== null; // semua user yang login bisa verifikasi checkout
    }
}
