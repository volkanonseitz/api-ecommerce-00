<?php

namespace App\Http\Controllers;

use App\DTO\RefundReasonData;
use App\Enums\Permission;
use App\Http\Requests\RefundReasonCreateRequest;
use App\Http\Requests\RefundReasonUpdateRequest;
use App\Http\Resources\RefundReasonResource;
use App\Models\RefundReason;
use App\Services\RefundReasonService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class RefundReasonController extends BaseController
{
    public function __construct(private RefundReasonService $service) {}

    public function index(Request $request)
    {
        $language = $request->language ?? config('shop.default_language', 'id');
        $limit = $request->limit ?? 15;
        $cacheKey = "refund_reasons_{$language}_{$limit}";
        $reasons = Cache::remember($cacheKey, 3600, function () use ($language, $limit) {
            return $this->service->getRefundReasons($language, $limit);
        });

        return $this->sendPaginated(
            $reasons,
            RefundReasonResource::collection($reasons->getCollection()),
            'Daftar alasan pengembalian berhasil diambil.'
        );
    }

    public function store(RefundReasonCreateRequest $request)
    {
        $user = $request->user();
        if (! $user || ! $user->hasPermissionTo(Permission::SUPER_ADMIN->value)) {
            throw new AuthorizationException(config('notice.NOT_AUTHORIZED'));
        }
        $data = RefundReasonData::fromRequest($request->validated());
        $reason = $this->service->create($data);
        Cache::forget("refund_reasons_{$data->language}_*");

        return $this->sendSuccess(new RefundReasonResource($reason), 'Refund reason created', 201);
    }

    public function show(Request $request, $params)
    {
        $language = $request->language ?? config('shop.default_language', 'id');
        $reason = $this->service->find($params, $language);

        return $this->sendSuccess(new RefundReasonResource($reason), 'Refund reason detail');
    }

    public function update(RefundReasonUpdateRequest $request, $id)
    {
        $user = $request->user();
        if (! $user || ! $user->hasPermissionTo(Permission::SUPER_ADMIN->value)) {
            throw new AuthorizationException(config('notice.NOT_AUTHORIZED'));
        }
        $reason = RefundReason::findOrFail($id);
        $data = RefundReasonData::fromRequest($request->validated());
        $updated = $this->service->update($reason, $data);
        Cache::forget("refund_reasons_{$data->language}_*");

        return $this->sendSuccess(new RefundReasonResource($updated), 'Refund reason updated');
    }

    public function destroy(Request $request, $id)
    {
        $user = $request->user();
        if (! $user || ! $user->hasPermissionTo(Permission::SUPER_ADMIN->value)) {
            throw new AuthorizationException(config('notice.NOT_AUTHORIZED'));
        }
        $reason = RefundReason::findOrFail($id);
        $language = $reason->language;
        $this->service->delete($reason);
        Cache::forget("refund_reasons_{$language}_*");

        return $this->sendSuccess(null, 'Refund reason deleted');
    }
}
