<?php

namespace App\Services;

use App\Models\RefundPolicy;
use App\Models\Shop;
use App\DTO\RefundPolicyData;
use App\Actions\CreateRefundPolicyAction;
use App\Actions\UpdateRefundPolicyAction;
use App\Enums\Permission;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\Request;

class RefundPolicyService
{
    public function __construct(
        private CreateRefundPolicyAction $createPolicy,
        private UpdateRefundPolicyAction $updatePolicy,
    ) {}

    public function hasPermission(?Authenticatable $user, ?int $shopId): bool
    {
        if (!$user) return false;
        if ($user->hasPermissionTo(Permission::SUPER_ADMIN->value)) return true;
        if (!$shopId) return false;

        $shop = Shop::find($shopId);
        if (!$shop) return false;

        if ($user->hasPermissionTo(Permission::STORE_OWNER->value)) {
            return $shop->owner_id === $user->id;
        }
        // Staff tidak bisa mengelola refund policy (hanya admin & store owner)
        return false;
    }

    public function getPoliciesQuery(Request $request)
    {
        $language = $request->language ?? config('shop.default_language', 'en');
        return RefundPolicy::where('language', $language);
    }

    public function findPolicy($value, string $language): RefundPolicy
    {
        if (is_numeric($value)) {
            return RefundPolicy::where('id', $value)->where('language', $language)->firstOrFail();
        }
        return RefundPolicy::where('slug', $value)->where('language', $language)->firstOrFail();
    }

    public function createPolicy(RefundPolicyData $data): RefundPolicy
    {
        return $this->createPolicy->execute($data);
    }

    public function updatePolicy(RefundPolicy $policy, RefundPolicyData $data): RefundPolicy
    {
        return $this->updatePolicy->execute($policy, $data);
    }

    public function deletePolicy(RefundPolicy $policy): void
    {
        $policy->delete();
    }
}