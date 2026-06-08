<?php

namespace App\Http\Controllers;

use App\Services\StoreNoticeService;
use App\Http\Requests\StoreNoticeRequest;
use App\Http\Requests\StoreNoticeUpdateRequest;
use App\Http\Resources\StoreNoticeResource;
use App\DTO\StoreNoticeData;
use App\Models\StoreNotice;
use App\Enums\StoreNoticeType;
use Illuminate\Http\Request;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Validation\ValidationException;

class StoreNoticeController extends Controller
{
    public function __construct(private StoreNoticeService $service) {}

    public function index(Request $request)
    {
        $limit = $request->limit ?? 15;
        $storeNotices = $this->service->getStoreNoticesQuery($request, $request->user())
            ->paginate($limit);
        return StoreNoticeResource::collection($storeNotices);
    }

    public function store(StoreNoticeRequest $request)
    {
        $user = $request->user();
        $shopId = $request->received_by[0] ?? 0;
        if (!$user->hasPermissionTo(Permission::SUPER_ADMIN->value) && !$this->service->hasPermission($user, $shopId)) {
            throw new AuthorizationException(config('notice.NOT_AUTHORIZED'));
        }
        $data = StoreNoticeData::fromRequest($request->validated());
        $storeNotice = $this->service->createStoreNotice($data, $user);
        return new StoreNoticeResource($storeNotice);
    }

    public function getStoreNoticeType(Request $request)
    {
        return response()->json($this->service->getStoreNoticeTypes($request->user()));
    }

    public function getUsersToNotify(Request $request)
    {
        $type = $request->type;
        if (in_array($type, [StoreNoticeType::ALL_SHOP->value, StoreNoticeType::ALL_VENDOR->value])) {
            throw new \Exception(config('notice.ACTION_NOT_VALID'), 400);
        }
        $users = $this->service->getUsersToNotify($request, $request->user());
        return response()->json($users);
    }

    public function show(Request $request, $id)
    {
        $storeNotice = StoreNotice::with(['creator', 'users', 'shops', 'read_status'])->findOrFail($id);
        return new StoreNoticeResource($storeNotice);
    }

    public function update(StoreNoticeUpdateRequest $request, $id)
    {
        $user = $request->user();
        $shopId = $request->received_by[0] ?? 0;
        if (!$user->hasPermissionTo(Permission::SUPER_ADMIN->value) && !$this->service->hasPermission($user, $shopId)) {
            throw new AuthorizationException(config('notice.NOT_AUTHORIZED'));
        }
        $storeNotice = StoreNotice::findOrFail($id);
        $data = StoreNoticeData::fromRequest($request->validated());
        $updated = $this->service->updateStoreNotice($storeNotice, $data, $user);
        return new StoreNoticeResource($updated);
    }

    public function destroy(Request $request, $id)
    {
        $storeNotice = StoreNotice::findOrFail($id);
        $this->service->deleteStoreNotice($storeNotice);
        return response()->json(['message' => 'Store notice deleted']);
    }

    public function readNotice(Request $request)
    {
        $request->validate(['id' => 'required|exists:store_notices,id']);
        $notice = StoreNotice::findOrFail($request->id);
        $this->service->markAsRead($notice, $request->user()->id);
        return response()->json(['success' => true]);
    }

    public function readAllNotice(Request $request)
    {
        $request->validate([
            'notices' => 'required|array|min:1',
            'notices.*' => 'exists:store_notices,id',
        ]);
        $this->service->markMultipleAsRead($request->notices, $request->user()->id);
        return response()->json(['success' => true]);
    }
}