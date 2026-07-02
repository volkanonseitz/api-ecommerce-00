<?php

namespace App\Modules\Payment\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

final class PaymentFailed
{
    use Dispatchable, SerializesModels;

    public string $orderTrackingNumber;

    public array $paymentData;

    public function __construct(string $orderTrackingNumber, array $paymentData)
    {
        $this->orderTrackingNumber = $orderTrackingNumber;
        $this->paymentData = $paymentData;
    }
}
