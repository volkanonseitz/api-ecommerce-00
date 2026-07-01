<?php

namespace App\Actions;

use App\Modules\Refund\DTO\RefundData;
use App\Models\Refund;

class UpdateRefundAction
{
    public function execute(Refund $refund, RefundData $data): Refund
    {
        $refund->update($data->toArray());

        return $refund->fresh();
    }
}
