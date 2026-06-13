<?php

namespace App\Http\Controllers;

use App\Services\BecameSellerService;
use App\Services\CommissionService;
use App\Http\Requests\BecameSellersRequest;
use App\DTO\BecameSellerData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class BecameSellerController extends Controller
{
    public function __construct(
        private BecameSellerService $becameSellerService,
        private CommissionService $commissionService
    ) {}

    /**
     * GET /became-sellers
     */
    public function index(Request $request)
    {
        $language = $request->language ?? config('shop.default_language', 'id');
        $cacheKey = 'cached_became_seller_' . $language;
        return Cache::rememberForever($cacheKey, function () use ($language) {
            return [
                'page_options' => $this->becameSellerService->getData($language),
                'commissions' => $this->commissionService->getAll(),
            ];
        });
    }

    /**
     * POST /became-sellers
     */
    public function store(BecameSellersRequest $request)
    {
        $language = $request->language ?? config('shop.default_language', 'id');
        $cacheKey = 'cached_became_seller_' . $language;
        Cache::forget($cacheKey);

        // Store commissions
        if ($request->has('commissions')) {
            $this->commissionService->storeCommissions($request->commissions, $language);
        }

        // Store/update became seller page options
        $data = BecameSellerData::fromRequest($request->only(['page_options', 'language']));
        $becomeSeller = $this->becameSellerService->storeOrUpdate($data);
        return response()->json($becomeSeller);
    }

    /**
     * GET /became-sellers/{id}
     */
    public function show($id)
    {
        $settings = $this->becameSellerService->getFirst();
        if (!$settings) {
            abort(404, config('notice.NOT_FOUND'));
        }
        return response()->json($settings);
    }

    /**
     * PUT /became-sellers/{id}
     */
    public function update(BecameSellersRequest $request, $id)
    {
        $language = $request->language ?? config('shop.default_language', 'id');
        $data = BecameSellerData::fromRequest($request->only(['page_options', 'language']));
        $updated = $this->becameSellerService->storeOrUpdate($data);
        return response()->json($updated);
    }

    /**
     * DELETE /became-sellers/{id}
     */
    public function destroy($id)
    {
        throw new \Exception(config('notice.ACTION_NOT_VALID'));
    }
}