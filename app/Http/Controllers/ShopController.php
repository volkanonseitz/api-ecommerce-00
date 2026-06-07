<?php

namespace App\Http\Controllers;

use App\Services\ShopService;
use App\Http\Requests\ShopCreateRequest;
use App\Http\Requests\ShopUpdateRequest;
use App\Http\Requests\TransferShopOwnerShipRequest;
use App\Http\Requests\UserCreateRequest;
use App\Http\Resources\ShopResource;
use App\DTO\ShopData;
use App\Models\Shop;
use App\Enums\Permission;
use Illuminate\Http\Request;
use Illuminate\Auth\Access\AuthorizationException;

class ShopController extends Controller
{
    public function __construct(private ShopService $shopService) {}

    public function index(Request $request)
    {
        $limit = $request->limit ?? 15;
        $shops = $this->shopService->getShopsQuery($request)->paginate($limit);
        return ShopResource::collection($shops);
    }

    public function store(ShopCreateRequest $request)
    {
        $user = $request->user();
        if (!$user->hasPermissionTo(Permission::STORE_OWNER->value)) {
            throw new AuthorizationException(config('notice.NOT_AUTHORIZED'));
        }
        
        // Membuat DTO dari request lalu menyuntikkan owner_id user yang sedang login
        $data = ShopData::fromRequest($request->validated())->withOwnerId($user->id);
        
        $shop = $this->shopService->createShop($data);
        return new ShopResource($shop);
    }

    public function show($slug, Request $request)
    {
        $shop = $this->shopService->getShopByIdOrSlug($slug, $request->user());
        return new ShopResource($shop);
    }

    public function update(ShopUpdateRequest $request, $id)
    {
        $user = $request->user();
        $shop = Shop::findOrFail($id);
        if (!$user->hasPermissionTo(Permission::SUPER_ADMIN->value) &&
            !($user->hasPermissionTo(Permission::STORE_OWNER->value) && $user->shops->contains($id))) {
            throw new AuthorizationException(config('notice.NOT_AUTHORIZED'));
        }
        $data = ShopData::fromRequest($request->validated());
        $updated = $this->shopService->updateShop($shop, $data);
        return new ShopResource($updated);
    }

    public function destroy(Request $request, $id)
    {
        $user = $request->user();
        $shop = Shop::findOrFail($id);
        if (!$user->hasPermissionTo(Permission::SUPER_ADMIN->value) &&
            !($user->hasPermissionTo(Permission::STORE_OWNER->value) && $user->shops->contains($id))) {
            throw new AuthorizationException(config('notice.NOT_AUTHORIZED'));
        }
        $this->shopService->deleteShop($shop);
        return response()->json(['message' => 'Shop deleted successfully']);
    }

    public function approveShop(Request $request)
    {
        if (!$request->user()->hasPermissionTo(Permission::SUPER_ADMIN->value)) {
            throw new AuthorizationException(config('notice.NOT_AUTHORIZED'));
        }
        $shop = Shop::findOrFail($request->id);
        $this->shopService->approveShop($shop, $request->admin_commission_rate, $request->isCustomCommission ?? false);
        return new ShopResource($shop);
    }

    public function disApproveShop(Request $request)
    {
        if (!$request->user()->hasPermissionTo(Permission::SUPER_ADMIN->value)) {
            throw new AuthorizationException(config('notice.NOT_AUTHORIZED'));
        }
        $shop = Shop::findOrFail($request->id);
        $this->shopService->disapproveShop($shop);
        return new ShopResource($shop);
    }

    public function addStaff(UserCreateRequest $request)
    {
        if (!$this->shopService->hasPermission($request->user(), $request->shop_id)) {
            throw new AuthorizationException(config('notice.NOT_AUTHORIZED'));
        }
        $shop = Shop::findOrFail($request->shop_id);
        $staff = $this->shopService->addStaff($shop, $request->validated());
        return response()->json(['success' => true, 'staff' => $staff]);
    }

    public function deleteStaff(Request $request, $id)
    {
        $staff = \App\Models\User::findOrFail($id);
        $user = $request->user();
        if (!$user->hasPermissionTo(Permission::STORE_OWNER->value) ||
            !$user->shops->contains('id', $staff->shop_id)) {
            throw new AuthorizationException(config('notice.NOT_AUTHORIZED'));
        }
        $this->shopService->removeStaff($staff);
        return response()->json(['success' => true]);
    }

    public function myShops(Request $request)
    {
        $shops = $request->user()->shops;
        return ShopResource::collection($shops);
    }

    public function followedShopsPopularProducts(Request $request)
    {
        $request->validate(['limit' => 'nullable|numeric']);
        $limit = $request->limit ?? 10;
        $products = $this->shopService->getFollowedShopsPopularProducts($request->user(), $limit);
        return response()->json($products);
    }

    public function userFollowedShops(Request $request)
    {
        $limit = $request->limit ?? 15;
        $shops = $this->shopService->getUserFollowedShops($request->user(), $limit);
        return ShopResource::collection($shops);
    }

    public function userFollowedShop(Request $request)
    {
        $request->validate(['shop_id' => 'required|numeric']);
        $isFollowing = $this->shopService->isUserFollowingShop($request->user(), (int)$request->shop_id);
        return response()->json($isFollowing);
    }

    public function handleFollowShop(Request $request)
    {
        $request->validate(['shop_id' => 'required|numeric']);
        $result = $this->shopService->toggleFollowShop($request->user(), (int)$request->shop_id);
        return response()->json($result);
    }

    public function nearByShop($lat, $lng, Request $request)
    {
        $settings = \App\Models\Settings::getData();
        $maxDistance = $settings->options['maxShopDistance'] ?? 1000;
        $shops = $this->shopService->findNearbyShops((float)$lat, (float)$lng, (float)$maxDistance);
        return response()->json($shops);
    }

    public function newOrInActiveShops(Request $request)
    {
        $limit = $request->limit ?? 15;
        $isActive = filter_var($request->is_active, FILTER_VALIDATE_BOOLEAN);
        $shops = Shop::withCount(['orders', 'products'])
            ->with(['owner.profile'])
            ->where('is_active', $isActive)
            ->paginate($limit);
        return ShopResource::collection($shops);
    }

    public function transferShopOwnership(TransferShopOwnerShipRequest $request)
    {
        $shop = Shop::findOrFail($request->shop_id);
        if (!$this->shopService->hasPermission($request->user(), $shop->id)) {
            throw new AuthorizationException(config('notice.NOT_AUTHORIZED'));
        }
        $newOwner = \App\Models\User::findOrFail($request->vendor_id);
        $this->shopService->transferShopOwnership($shop, $newOwner, $request->user(), $request->message, $request->vendorMessage);
        return response()->json(['message' => 'Ownership transfer initiated']);
    }

    public function shopMaintenanceEvent(Request $request)
    {
        $request->validate(['shop_id' => 'required|exists:shops,id']);
        $shop = Shop::findOrFail($request->shop_id);
        if (!$this->shopService->hasPermission($request->user(), $shop->id)) {
            throw new AuthorizationException(config('notice.NOT_AUTHORIZED'));
        }
        if ($request->isShopUnderMaintenance && $request->isMaintenance) {
            $this->shopService->enableMaintenance($shop);
        } else {
            $this->shopService->disableMaintenance($shop);
        }
        return response()->json(['success' => true]);
    }
}