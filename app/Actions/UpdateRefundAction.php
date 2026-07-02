<?php

namespace App\Actions;

use App\Models\Refund;
use App\Modules\Refund\DTO\RefundData;

class UpdateRefundAction
{
    public function execute(Refund $refund, RefundData $data): Refund
    {
        $refund->update($data->toArray());

        return $refund->fresh();
    }
}
