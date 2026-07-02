<?php

declare(strict_types=1);

namespace App\Modules\Shop\Http\Controllers;

use App\Http\Controllers\BaseController;
use App\Models\Shop;
use App\Models\User;
use App\Modules\OwnershipTransfer\Http\Requests\OwnershipTransferRequest;
use App\Modules\Shop\Actions\AddStaffAction;
use App\Modules\Shop\Actions\ApproveShopAction;
use App\Modules\Shop\Actions\CreateShopAction;
use App\Modules\Shop\Actions\DeleteShopAction;
use App\Modules\Shop\Actions\DisableShopMaintenanceAction;
use App\Modules\Shop\Actions\DisapproveShopAction;
use App\Modules\Shop\Actions\EnableShopMaintenanceAction;
use App\Modules\Shop\Actions\RemoveStaffAction;
use App\Modules\Shop\Actions\ToggleFollowShopAction;
use App\Modules\Shop\Actions\TransferShopOwnershipAction;
use App\Modules\Shop\Actions\UpdateShopAction;
use App\Modules\Shop\DTO\ApproveShopData;
use App\Modules\Shop\DTO\ShopData;
use App\Modules\Shop\DTO\StaffData;
use App\Modules\Shop\Http\Requests\AddStaffRequest;
use App\Modules\Shop\Http\Requests\ApproveShopRequest;
use App\Modules\Shop\Http\Requests\FollowShopRequest;
use App\Modules\Shop\Http\Requests\NearbyShopRequest;
use App\Modules\Shop\Http\Requests\RemoveStaffRequest;
use App\Modules\Shop\Http\Requests\ShopCreateRequest;
use App\Modules\Shop\Http\Requests\ShopMaintenanceRequest;
use App\Modules\Shop\Http\Requests\ShopUpdateRequest;
use App\Modules\Shop\Http\Resources\ShopResource;
use App\Modules\Shop\Services\ShopQueryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ShopController extends BaseController
{
    public function __construct(private readonly ShopQueryService $shops) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $limit = (int) ($request->integer('limit') ?: 15);
        $shops = $this->shops->listQuery()->paginate($limit);

        return ShopResource::collection($shops);
    }

    public function store(ShopCreateRequest $request, CreateShopAction $action): ShopResource
    {
        $data = ShopData::fromValidated($request->validated())
            ->withOwnerId($request->user()->id);

        $shop = $action->execute($data);

        return new ShopResource($shop);
    }

    public function show(string $slug, Request $request): ShopResource
    {
        $shop = $this->shops->findByIdOrSlug($slug, $request->user());

        return new ShopResource($shop);
    }

    /**
     * Route model binding `{shop}` -> otomatis 404 kalau tidak ada,
     * dan dipakai FormRequest untuk resolve Policy check (lihat ShopUpdateRequest).
     */
    public function update(ShopUpdateRequest $request, Shop $shop, UpdateShopAction $action): ShopResource
    {
        $data = ShopData::fromValidated($request->validated());
        $updated = $action->execute($shop, $data);

        return new ShopResource($updated);
    }

    public function destroy(Shop $shop, DeleteShopAction $action): JsonResponse
    {
        $this->authorize('delete', $shop);

        $action->execute($shop);

        return response()->json(['message' => 'Shop deleted successfully']);
    }

    public function approveShop(ApproveShopRequest $request, Shop $shop, ApproveShopAction $action): ShopResource
    {
        $data = ApproveShopData::fromValidated($request->validated());
        $approved = $action->execute($shop, $data);

        return new ShopResource($approved);
    }

    public function disApproveShop(Shop $shop, DisapproveShopAction $action): ShopResource
    {
        $this->authorize('approve', $shop);

        $disapproved = $action->execute($shop);

        return new ShopResource($disapproved);
    }

    public function addStaff(AddStaffRequest $request, Shop $shop, AddStaffAction $action): JsonResponse
    {
        $data = StaffData::fromValidated($request->validated(), $shop->id);
        $staff = $action->execute($data);

        return response()->json(['success' => true, 'staff' => $staff]);
    }

    public function deleteStaff(RemoveStaffRequest $request, User $staff, RemoveStaffAction $action): JsonResponse
    {
        $action->execute($staff);

        return response()->json(['success' => true]);
    }

    public function myShops(Request $request): AnonymousResourceCollection
    {
        return ShopResource::collection($request->user()->shops);
    }

    public function followedShopsPopularProducts(Request $request): JsonResponse
    {
        $limit = (int) ($request->integer('limit') ?: 10);
        $products = $this->shops->followedShopsPopularProducts($request->user(), $limit);

        return response()->json($products);
    }

    public function userFollowedShops(Request $request): AnonymousResourceCollection
    {
        $limit = (int) ($request->integer('limit') ?: 15);
        $shops = $this->shops->followedShops($request->user(), $limit);

        return ShopResource::collection($shops);
    }

    public function userFollowedShop(FollowShopRequest $request): JsonResponse
    {
        $isFollowing = $this->shops->isFollowing($request->user(), (int) $request->validated('shop_id'));

        return response()->json($isFollowing);
    }

    public function handleFollowShop(FollowShopRequest $request, ToggleFollowShopAction $action): JsonResponse
    {
        $result = $action->execute($request->user(), (int) $request->validated('shop_id'));

        return response()->json($result);
    }

    public function nearByShop(NearbyShopRequest $request): JsonResponse
    {
        $shops = $this->shops->findNearby(
            (float) $request->validated('lat'),
            (float) $request->validated('lng'),
        );

        return response()->json($shops);
    }

    public function newOrInActiveShops(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Shop::class);

        $limit = (int) ($request->integer('limit') ?: 15);
        $isActive = $request->boolean('is_active');
        $shops = $this->shops->findNewOrInactive($isActive, $limit);

        return ShopResource::collection($shops);
    }

    public function transferShopOwnership(OwnershipTransferRequest $request, TransferShopOwnershipAction $action): JsonResponse
    {
        $shop = Shop::findOrFail($request->validated('shop_id'));
        $this->authorize('transferOwnership', $shop);

        $newOwner = User::findOrFail($request->validated('vendor_id'));

        $action->execute(
            $shop,
            $newOwner,
            $request->user(),
            $request->validated('message'),
            $request->validated('vendorMessage'),
        );

        return response()->json(['message' => 'Ownership transfer initiated']);
    }

    public function shopMaintenanceEvent(
        ShopMaintenanceRequest $request,
        Shop $shop,
        EnableShopMaintenanceAction $enable,
        DisableShopMaintenanceAction $disable,
    ): JsonResponse {
        if ($request->boolean('enable')) {
            $enable->execute($shop);
        } else {
            $disable->execute($shop);
        }

        return response()->json(['success' => true]);
    }
}
