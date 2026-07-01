<?php

namespace App\Actions;

use App\Modules\Refund\DTO\RefundPolicyData;
use App\Models\RefundPolicy;

class UpdateRefundPolicyAction
{
    public function execute(RefundPolicy $policy, RefundPolicyData $data): RefundPolicy
    {
        $policy->update($data->toArray());

        return $policy->fresh();
    }
}
