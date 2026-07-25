<?php

namespace App\Modules\Payment\Events;

use App\Models\Order;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

final class PaymentSuccess
{
    use Dispatchable, SerializesModels;

    public Order $order; // Ubah dari string $orderTrackingNumber

    public array $paymentData;

    public function __construct(Order $order, array $paymentData) // Ubah parameter
    {
        $this->order = $order; // Ubah penugasan
        $this->paymentData = $paymentData;
    }
}
