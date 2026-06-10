<?php

namespace App\Events;

use App\Models\Refund;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RefundRequested
{
    use Dispatchable, SerializesModels;

    public Refund $refund;

    public function __construct(Refund $refund)
    {
        $this->refund = $refund;
    }
}