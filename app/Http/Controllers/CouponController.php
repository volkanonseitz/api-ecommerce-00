<?php

namespace App\Http\Controllers;

use App\Services\CouponService;
use App\Http\Requests\CouponCreateRequest;
use App\Http\Requests\CouponUpdateRequest;
use App\Http\Resources\CouponResource;
use App\DTO\CouponData;
use App\Models\Coupon;
use App\Enums\Permission;
use Illuminate\Http\Request;
use Illuminate\Auth\Access\AuthorizationException;

class CouponController extends Controller
{
    public function __construct(private CouponService $couponService) {}

    /**
     * GET /coupons
     */
    public function index(Request $request)
    {
        $limit = $request->limit ?? 15;
        $query = $this->couponService->getCouponsQuery($request, $request->user());
        $coupons = $query->paginate($limit);
        $data = CouponResource::collection($coupons)->response()->getData(true);
        return formatAPIResourcePaginate($data);
    }

    /**
     * POST /coupons
     */
    public function store(CouponCreateRequest $request)
    {
        $user = $request->user();
        $isSuperAdmin = $user && $user->hasPermissionTo(Permission::SUPER_ADMIN->value);

        // Validasi permission jika ada shop_id
        if ($request->shop_id && !$this->couponService->hasPermission($user, $request->shop_id)) {
            throw new AuthorizationException(config('notice.NOT_AUTHORIZED'));
        }

        $data = CouponData::fromRequest($request->validated(), $user?->id);
        $coupon = $this->couponService->createCoupon($data, $isSuperAdmin);
        return new CouponResource($coupon);
    }

    /**
     * GET /coupons/{params}  (params bisa id atau code)
     */
    public function show(Request $request, $params)
    {
        $language = $request->language ?? config('shop.default_language', 'id');
        $coupon = $this->couponService->findCoupon($params, $language);
        return new CouponResource($coupon);
    }

    /**
     * POST /coupons/verify
     */
    public function verify(Request $request)
    {
        $request->validate([
            'code' => 'required|string',
            'sub_total' => 'required|numeric',
        ]);
        $result = $this->couponService->verifyCoupon(
            $request->code,
            $request->sub_total,
            $request->item,
            $request->user()
        );
        return response()->json($result);
    }

    /**
     * PUT /coupons/{id}
     */
    public function update(CouponUpdateRequest $request, $id)
    {
        $coupon = Coupon::findOrFail($id);
        $user = $request->user();
        $isSuperAdmin = $user && $user->hasPermissionTo(Permission::SUPER_ADMIN->value);

        // Permission: hanya super admin atau pemilik shop yang bisa update
        if (!$isSuperAdmin && !$this->couponService->hasPermission($user, $coupon->shop_id)) {
            throw new AuthorizationException(config('notice.NOT_AUTHORIZED'));
        }

        $data = CouponData::fromRequest($request->validated(), $user?->id);
        $updated = $this->couponService->updateCoupon($coupon, $data, $isSuperAdmin);
        return new CouponResource($updated);
    }

    /**
     * DELETE /coupons/{id}
     */
    public function destroy($id)
    {
        $coupon = Coupon::findOrFail($id);
        $this->couponService->deleteCoupon($coupon);
        return response()->json(['message' => 'Coupon deleted successfully']);
    }

    /**
     * POST /coupons/approve
     */
    public function approveCoupon(Request $request)
    {
        $user = $request->user();
        if (!$user || !$user->hasPermissionTo(Permission::SUPER_ADMIN->value)) {
            throw new AuthorizationException(config('notice.NOT_AUTHORIZED'));
        }
        $coupon = Coupon::findOrFail($request->id);
        $this->couponService->approveCoupon($coupon);
        return new CouponResource($coupon);
    }

    /**
     * POST /coupons/disapprove
     */
    public function disApproveCoupon(Request $request)
    {
        $user = $request->user();
        if (!$user || !$user->hasPermissionTo(Permission::SUPER_ADMIN->value)) {
            throw new AuthorizationException(config('notice.NOT_AUTHORIZED'));
        }
        $coupon = Coupon::findOrFail($request->id);
        $this->couponService->disapproveCoupon($coupon);
        return new CouponResource($coupon);
    }
}