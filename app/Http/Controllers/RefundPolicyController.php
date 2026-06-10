<?php

namespace App\Http\Controllers;

use App\Services\RefundPolicyService;
use App\Http\Requests\StoreRefundPolicyRequest;
use App\Http\Requests\UpdateRefundPolicyRequest;
use App\Http\Resources\RefundPolicyResource;
use App\DTO\RefundPolicyData;
use App\Models\RefundPolicy;
use App\Enums\Permission;
use Illuminate\Http\Request;
use Illuminate\Auth\Access\AuthorizationException;

class RefundPolicyController extends Controller
{
    public function __construct(private RefundPolicyService $policyService) {}

    /**
     * GET /refund-policies
     */
    public function index(Request $request)
    {
        $limit = $request->limit ?? 15;
        $policies = $this->policyService->getPoliciesQuery($request)->paginate($limit);
        $data = RefundPolicyResource::collection($policies)->response()->getData(true);
        return formatAPIResourcePaginate($data);
    }

    /**
     * POST /refund-policies
     */
    public function store(StoreRefundPolicyRequest $request)
    {
        $user = $request->user();
        $shopId = $request->shop_id;

        if (!$this->policyService->hasPermission($user, $shopId)) {
            throw new AuthorizationException(config('notice.NOT_AUTHORIZED'));
        }

        $data = RefundPolicyData::fromRequest($request->validated());
        $policy = $this->policyService->createPolicy($data);
        return new RefundPolicyResource($policy);
    }

    /**
     * GET /refund-policies/{slug}
     */
    public function show(Request $request, $slug)
    {
        $language = $request->language ?? config('shop.default_language', 'en');
        $policy = $this->policyService->findPolicy($slug, $language);
        return new RefundPolicyResource($policy);
    }

    /**
     * PUT /refund-policies/{id}
     */
    public function update(UpdateRefundPolicyRequest $request, $id)
    {
        $policy = RefundPolicy::findOrFail($id);
        $user = $request->user();
        $shopId = $request->shop_id ?? $policy->shop_id;

        if (!$this->policyService->hasPermission($user, $shopId)) {
            throw new AuthorizationException(config('notice.NOT_AUTHORIZED'));
        }

        $data = RefundPolicyData::fromRequest($request->validated());
        $updated = $this->policyService->updatePolicy($policy, $data);
        return new RefundPolicyResource($updated);
    }

    /**
     * DELETE /refund-policies/{id}
     */
    public function destroy(Request $request, $id)
    {
        $user = $request->user();
        if (!$user->hasPermissionTo(Permission::SUPER_ADMIN->value)) {
            throw new AuthorizationException(config('notice.NOT_AUTHORIZED'));
        }

        $policy = RefundPolicy::findOrFail($id);
        $this->policyService->deletePolicy($policy);
        return response()->json(['message' => 'Refund policy deleted successfully']);
    }
}