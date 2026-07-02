<?php

namespace App\Modules\StoreNotice\Http\Controllers;

use App\Enums\StoreNoticeType;
use App\Http\Controllers\BaseController;
use App\Models\StoreNotice;
use App\Modules\StoreNotice\Actions\CreateStoreNoticeAction;
use App\Modules\StoreNotice\Actions\DeleteStoreNoticeAction;
use App\Modules\StoreNotice\Actions\MarkMultipleStoreNoticesAsReadAction;
use App\Modules\StoreNotice\Actions\MarkStoreNoticeAsReadAction;
use App\Modules\StoreNotice\Actions\UpdateStoreNoticeAction;
use App\Modules\StoreNotice\DTO\StoreNoticeData;
use App\Modules\StoreNotice\Http\Requests\StoreNoticeRequest;
use App\Modules\StoreNotice\Http\Requests\StoreNoticeUpdateRequest;
use App\Modules\StoreNotice\Http\Resources\StoreNoticeResource;
use App\Modules\StoreNotice\Services\StoreNoticeQueryService;
use Illuminate\Http\Request;

class StoreNoticeController extends BaseController
{
    public function __construct(
        private readonly StoreNoticeQueryService $queryService,
        private readonly CreateStoreNoticeAction $createAction,
        private readonly UpdateStoreNoticeAction $updateAction,
        private readonly DeleteStoreNoticeAction $deleteAction,
        private readonly MarkStoreNoticeAsReadAction $markAsReadAction,
        private readonly MarkMultipleStoreNoticesAsReadAction $markMultipleAsReadAction,
    ) {}

    public function index(Request $request)
    {
        $this->authorize('viewAny', StoreNotice::class);

        $limit = $request->limit ?? 15;
        $storeNotices = $this->queryService->getStoreNoticesQuery($request, $request->user())
            ->paginate($limit);

        return StoreNoticeResource::collection($storeNotices);
    }

    public function store(StoreNoticeRequest $request)
    {
        $this->authorize('create', StoreNotice::class);

        $data = StoreNoticeData::fromRequest($request->validated());
        $storeNotice = $this->createAction->execute($data, $request->user());

        return new StoreNoticeResource($storeNotice);
    }

    public function getStoreNoticeType(Request $request)
    {
        $this->authorize('viewAny', StoreNotice::class); // Assuming any user who can view notices can view types

        return response()->json($this->queryService->getStoreNoticeTypes($request->user()));
    }

    public function getUsersToNotify(Request $request)
    {
        $this->authorize('create', StoreNotice::class); // Only users who can create notices can see who to notify
        $type = $request->type;
        if (in_array($type, [StoreNoticeType::ALL_SHOP->value, StoreNoticeType::ALL_VENDOR->value])) {
            throw new \Exception(config('notice.ACTION_NOT_VALID'), 400);
        }
        $users = $this->queryService->getUsersToNotify($request, $request->user());

        return response()->json($users);
    }

    public function show(Request $request, $id)
    {
        $storeNotice = $this->queryService->findOrFail((int) $id);
        $this->authorize('view', $storeNotice);

        return new StoreNoticeResource($storeNotice);
    }

    public function update(StoreNoticeUpdateRequest $request, $id)
    {
        $storeNotice = $this->queryService->findOrFail((int) $id);
        $this->authorize('update', $storeNotice);

        $data = StoreNoticeData::fromRequest($request->validated());
        $updated = $this->updateAction->execute($storeNotice, $data, $request->user());

        return new StoreNoticeResource($updated);
    }

    public function destroy(Request $request, $id)
    {
        $storeNotice = $this->queryService->findOrFail((int) $id);
        $this->authorize('delete', $storeNotice);

        $this->deleteAction->execute($storeNotice);

        return response()->json(['message' => 'Store notice deleted']);
    }

    public function readNotice(Request $request)
    {
        $request->validate(['id' => 'required|exists:store_notices,id']);
        $notice = $this->queryService->findOrFail((int) $request->id);
        $this->authorize('read', $notice); // Authorize if user can mark this specific notice as read

        $this->markAsReadAction->execute($notice, $request->user()->id);

        return response()->json(['success' => true]);
    }

    public function readAllNotice(Request $request)
    {
        $request->validate([
            'notices' => 'required|array|min:1',
            'notices.*' => 'exists:store_notices,id',
        ]);
        // Authorize if user can mark multiple notices as read (e.g., all notices they have access to)
        $this->authorize('readAny', StoreNotice::class);

        $this->markMultipleAsReadAction->execute($request->notices, $request->user()->id);

        return response()->json(['success' => true]);
    }
}
