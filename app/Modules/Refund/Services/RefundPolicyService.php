<?php

declare(strict_types=1);

namespace App\Modules\Refund\Services;

use App\Enums\Permission;
use App\Models\RefundPolicy;
use App\Models\Shop;
use App\Models\User;
use App\Modules\Refund\DTO\RefundPolicyData;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class RefundPolicyService
{
    public function getPoliciesQuery(Request $request, User $user): Builder
    {
        $language = $request->get('language', config('shop.default_language', 'id'));
        $query = RefundPolicy::where('language', $language);

        // Filter by shop_id if not super_admin
        if (!$user->hasPermissionTo('super_admin') && $request->has('shop_id')) {
            $query->where('shop_id', $request->get('shop_id'));
        }

        return $query;
    }

    public function findPolicy(string $value, string $language): RefundPolicy
    {
        if (is_numeric($value)) {
            return RefundPolicy::where('id', $value)->where('language', $language)->firstOrFail();
        }

        return RefundPolicy::where('slug', $value)->where('language', $language)->firstOrFail();
    }

    public function createPolicy(RefundPolicyData $data, User $user): RefundPolicy
    {
        $policyData = $data->toArray();
        if (!$user->hasPermissionTo(Permission::SUPER_ADMIN->value) && $user->hasPermissionTo(Permission::STORE_OWNER->value)) {
            $policyData['shop_id'] = $user->shops()->first()?->id;
        }

        $policy = RefundPolicy::create($policyData);
        return $policy;
    }

    public function updatePolicy(RefundPolicy $policy, RefundPolicyData $data, User $user): RefundPolicy
    {
        $policyData = $data->toArray();
        if (!$user->hasPermissionTo(Permission::SUPER_ADMIN->value) && $user->hasPermissionTo(Permission::STORE_OWNER->value)) {
            $policyData['shop_id'] = $user->shops()->first()?->id;
        }
        
        $policy->update($policyData);
        return $policy->fresh();
    }

    public function deletePolicy(RefundPolicy $policy, User $user): void
    {
        $policy->delete();
    }
}
