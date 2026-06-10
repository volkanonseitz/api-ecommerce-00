<?php

namespace App\Events;

use App\Models\PaymentMethod;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PaymentMethods
{
    use Dispatchable, SerializesModels;

    public PaymentMethod $paymentMethods;

    public function __construct(PaymentMethod $paymentMethods)
    {
        $this->paymentMethods = $paymentMethods;
    }
}