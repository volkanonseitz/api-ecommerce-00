<?php

namespace App\Actions;

use App\Models\RefundPolicy;
use App\DTO\RefundPolicyData;

class UpdateRefundPolicyAction
{
    public function execute(RefundPolicy $policy, RefundPolicyData $data): RefundPolicy
    {
        $policy->update($data->toArray());
        return $policy->fresh();
    }
}