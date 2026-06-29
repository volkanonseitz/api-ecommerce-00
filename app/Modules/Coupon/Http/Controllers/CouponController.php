<?php

declare(strict_types=1);

namespace App\Modules\Coupon\Http\Controllers;

use App\Enums\Permission;
use App\Http\Controllers\BaseController;
use App\Models\Coupon;
use App\Modules\Coupon\DTO\CouponData;
use App\Modules\Coupon\Http\Requests\CouponCreateRequest;
use App\Modules\Coupon\Http\Requests\CouponUpdateRequest;
use App\Modules\Coupon\Http\Resources\CouponResource;
use App\Modules\Coupon\Services\CouponService;
use Illuminate\Http\Request;

class CouponController extends BaseController
{
    public function __construct(private CouponService $couponService) {}

    public function index(Request $request)
    {
        $this->authorize('viewAny', Coupon::class);

        $limit = (int) ($request->limit ?? 15);
        $query = $this->couponService->getCouponsQuery($request, $request->user());
        $coupons = $query->paginate($limit);

        return CouponResource::collection($coupons);
    }

    public function store(CouponCreateRequest $request)
    {
        $user = $request->user();
        $shopId = $request->shop_id;
        $this->authorize('create', [Coupon::class, $shopId]);

        $isSuperAdmin = $user && $user->hasPermissionTo(Permission::SUPER_ADMIN->value);
        $data = CouponData::fromRequest($request->validated(), $user?->id);
        $coupon = $this->couponService->createCoupon($data, $isSuperAdmin);

        return new CouponResource($coupon);
    }

    public function show(Request $request, string $params)
    {
        $language = $request->language ?? config('shop.default_language', 'id');
        $coupon = $this->couponService->findCoupon($params, $language);
        $this->authorize('view', $coupon);

        return new CouponResource($coupon);
    }

    public function verify(Request $request)
    {
        $request->validate([
            'code' => 'required|string',
            'sub_total' => 'required|numeric',
        ]);

        $result = $this->couponService->verifyCoupon(
            $request->code,
            (float) $request->sub_total,
            $request->item,
            $request->user()
        );

        return response()->json($result);
    }

    public function update(CouponUpdateRequest $request, int $id)
    {
        $coupon = Coupon::findOrFail($id);
        $this->authorize('update', $coupon);

        $user = $request->user();
        $isSuperAdmin = $user && $user->hasPermissionTo(Permission::SUPER_ADMIN->value);
        $data = CouponData::fromRequest($request->validated(), $user?->id);
        $updated = $this->couponService->updateCoupon($coupon, $data, $isSuperAdmin);

        return new CouponResource($updated);
    }

    public function destroy(int $id)
    {
        $coupon = Coupon::findOrFail($id);
        $this->authorize('delete', $coupon);
        $this->couponService->deleteCoupon($coupon);

        return $this->sendSuccess(null, 'Coupon deleted successfully');
    }

    public function approveCoupon(Request $request)
    {
        $this->authorize('approve', Coupon::class);

        $request->validate(['id' => 'required|exists:coupons,id']);
        $coupon = Coupon::findOrFail($request->id);
        $this->couponService->approveCoupon($coupon);

        return new CouponResource($coupon);
    }

    public function disApproveCoupon(Request $request)
    {
        $this->authorize('disapprove', Coupon::class);

        $request->validate(['id' => 'required|exists:coupons,id']);
        $coupon = Coupon::findOrFail($request->id);
        $this->couponService->disapproveCoupon($coupon);

        return new CouponResource($coupon);
    }
}
