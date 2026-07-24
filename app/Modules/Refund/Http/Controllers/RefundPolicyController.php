<?php

declare(strict_types=1);

namespace App\Modules\Refund\Http\Controllers;

use App\Http\Controllers\BaseController;
use App\Models\RefundPolicy;
use App\Modules\Refund\DTO\RefundPolicyData;
use App\Modules\Refund\Http\Requests\RefundPolicyStoreRequest;
use App\Modules\Refund\Http\Requests\RefundPolicyUpdateRequest;
use App\Modules\Refund\Http\Resources\RefundPolicyResource;
use App\Modules\Refund\Services\RefundPolicyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RefundPolicyController extends BaseController
{
    public function __construct(private RefundPolicyService $policyService) {}

    public function index(Request $request): JsonResponse
    {
        $limit = (int) $request->get('limit', 15);
        $policies = $this->policyService->getPoliciesQuery($request, $request->user())->paginate($limit);

        return $this->sendPaginated(
            $policies,
            RefundPolicyResource::collection($policies->getCollection()),
            'Refund policies retrieved successfully'
        );
    }

    public function store(RefundPolicyStoreRequest $request): JsonResponse
    {
        $this->authorize('create', RefundPolicy::class);

        $data = RefundPolicyData::fromRequest($request);
        $policy = $this->policyService->createPolicy($data, $request->user());

        return $this->sendSuccess(
            new RefundPolicyResource($policy),
            'Refund policy created successfully',
            201
        );
    }

    public function show(Request $request, string $slug): JsonResponse
    {
        $language = $request->get('language', config('shop.default_language', 'id'));
        $policy = $this->policyService->findPolicy($slug, $language);

        return $this->sendSuccess(
            new RefundPolicyResource($policy),
            'Refund policy retrieved successfully'
        );
    }

    public function update(RefundPolicyUpdateRequest $request, int $id): JsonResponse
    {
        $policy = RefundPolicy::findOrFail($id);
        $this->authorize('update', $policy);

        $data = RefundPolicyData::fromRequest($request);
        $updated = $this->policyService->updatePolicy($policy, $data, $request->user());

        return $this->sendSuccess(
            new RefundPolicyResource($updated),
            'Refund policy updated successfully'
        );
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $policy = RefundPolicy::findOrFail($id);
        $this->authorize('delete', $policy);

        $this->policyService->deletePolicy($policy, $request->user());

        return $this->sendSuccess(
            null,
            'Refund policy deleted successfully'
        );
    }
}
