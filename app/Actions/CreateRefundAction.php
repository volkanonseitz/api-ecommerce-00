<?php

namespace App\Actions;

use App\DTO\RefundData;
use App\Models\Refund;

class CreateRefundAction
{
    public function execute(RefundData $data): Refund
    {
        return Refund::create($data->toArray());
    }
}
