<?php

declare(strict_types=1);

namespace App\Modules\PaymentMethod\Policies;

use App\Models\PaymentMethod;
use App\Models\User;

class PaymentMethodPolicy
{
    public function viewAny(User $user): bool
    {
        return $user !== null;
    }

    public function view(User $user, PaymentMethod $method): bool
    {
        return $method->paymentGateway && $method->paymentGateway->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $user !== null;
    }

    public function delete(User $user, PaymentMethod $method): bool
    {
        return $this->view($user, $method);
    }

    public function setDefault(User $user, PaymentMethod $method): bool
    {
        return $this->view($user, $method);
    }
}
