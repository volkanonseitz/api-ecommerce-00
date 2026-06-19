<?php

namespace App\Actions;

use App\DTO\RefundPolicyData;
use App\Models\RefundPolicy;

class CreateRefundPolicyAction
{
    public function execute(RefundPolicyData $data): RefundPolicy
    {
        return RefundPolicy::create($data->toArray());
    }
}
