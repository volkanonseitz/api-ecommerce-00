<?php

declare(strict_types=1);

namespace App\Modules\PaymentIntent\Policies;

use App\Models\PaymentIntent;
use App\Models\User;

class PaymentIntentPolicy // untuk saat ini tidak digunakan karena otorisasi berbasis guest checkout
{
    public function view(User $user, PaymentIntent $intent): bool
    {
        // User can view if they own the order
        $order = $intent->order;
        if ($order && $order->customer_id === $user->id) {
            return true;
        }

        // Or if user is shop owner/staff (for shop-related)
        // ... bisa ditambahkan sesuai kebutuhan

        return false;
    }
}
