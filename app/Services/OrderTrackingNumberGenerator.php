<?php

declare(strict_types=1);

namespace App\Services\Order;

use App\Models\Order;

class OrderTrackingNumberGenerator
{
    public function generate(): string
    {
        $today = date('Ymd');
        do {
            $trackingNumber = $today . random_int(100000, 999999);
        } while (Order::where('tracking_number', $trackingNumber)->exists());

        return $trackingNumber;
    }
}