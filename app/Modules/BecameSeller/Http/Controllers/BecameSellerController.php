<?php

declare(strict_types=1);

namespace App\Modules\BecameSeller\Http\Controllers;

use App\Http\Controllers\BaseController;
use App\Models\BecameSeller;
use App\Modules\BecameSeller\DTO\BecameSellerData;
use App\Modules\BecameSeller\Http\Requests\BecameSellersRequest;
use App\Modules\BecameSeller\Services\BecameSellerService;
use App\Services\CommissionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class BecameSellerController extends BaseController
{
    public function __construct(
        private BecameSellerService $becameSellerService,
        private CommissionService $commissionService
    ) {}

    public function index(Request $request)
    {
        $this->authorize('viewAny', BecameSeller::class);
        $language = $request->language ?? config('shop.default_language', 'id');
        $cacheKey = 'cached_became_seller_'.$language;
        $data = Cache::rememberForever($cacheKey, function () use ($language) {
            return [
                'page_options' => $this->becameSellerService->getData($language),
                'commissions' => $this->commissionService->getAll(),
            ];
        });

        return $this->sendSuccess($data, 'Became seller data');
    }

    public function store(BecameSellersRequest $request)
    {
        $this->authorize('create', BecameSeller::class);
        $language = $request->language ?? config('shop.default_language', 'id');
        $cacheKey = 'cached_became_seller_'.$language;
        Cache::forget($cacheKey);

        if ($request->has('commissions')) {
            $this->commissionService->storeCommissions($request->commissions, $language);
        }

        $data = BecameSellerData::fromRequest($request->only(['page_options', 'language']));
        $becomeSeller = $this->becameSellerService->storeOrUpdate($data);

        return $this->sendSuccess($becomeSeller, 'Became seller data saved', 201);
    }

    public function show($id)
    {
        $settings = $this->becameSellerService->getFirst();
        if (! $settings) {
            return $this->sendError('Settings not found', 404);
        }

        return $this->sendSuccess($settings, 'Became seller detail');
    }

    public function update(BecameSellersRequest $request, $id)
    {
        $this->authorize('update', BecameSeller::class);
        $language = $request->language ?? config('shop.default_language', 'id');
        $data = BecameSellerData::fromRequest($request->only(['page_options', 'language']));
        $updated = $this->becameSellerService->storeOrUpdate($data);
        Cache::forget('cached_became_seller_'.$language);

        return $this->sendSuccess($updated, 'Became seller data updated');
    }

    public function destroy($id)
    {
        throw new \Exception(config('notice.ACTION_NOT_VALID'));
    }
}
