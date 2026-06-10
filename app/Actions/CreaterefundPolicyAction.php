<?php

namespace App\Actions;

use App\Models\RefundPolicy;
use App\DTO\RefundPolicyData;

class CreateRefundPolicyAction
{
    public function execute(RefundPolicyData $data): RefundPolicy
    {
        return RefundPolicy::create($data->toArray());
    }
}