<?php

namespace App\Actions;

use App\Models\Refund;
use App\DTO\RefundData;

class CreateRefundAction
{
    public function execute(RefundData $data): Refund
    {
        return Refund::create($data->toArray());
    }
}