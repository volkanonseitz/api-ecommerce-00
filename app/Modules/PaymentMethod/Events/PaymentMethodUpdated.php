<?php

namespace App\Modules\PaymentMethod\Events;

use App\Models\PaymentMethod;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

final class PaymentMethodUpdated
{
    use Dispatchable, SerializesModels;

    public PaymentMethod $paymentMethod;

    public function __construct(PaymentMethod $paymentMethod)
    {
        $this->paymentMethod = $paymentMethod;
    }
}
