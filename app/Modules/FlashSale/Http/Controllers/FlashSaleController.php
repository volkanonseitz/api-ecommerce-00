<?php

declare(strict_types=1);

namespace App\Modules\FlashSale\Http\Controllers;

use App\Http\Controllers\BaseController;
use App\Models\FlashSale;
use App\Models\Product; // Needed for ProductResource
use App\Modules\FlashSale\DTO\FlashSaleData;
use App\Modules\FlashSale\Http\Requests\FlashSaleCreateRequest;
use App\Modules\FlashSale\Http\Requests\FlashSaleUpdateRequest;
use App\Modules\FlashSale\Http\Resources\FlashSaleResource;
use App\Modules\FlashSale\Services\FlashSaleQueryService; // New Query Service
use App\Modules\FlashSale\Services\FlashSaleWriteService; // New Write Service
// New Delete Action
use App\Modules\Product\Http\Resources\ProductResource; // Assuming ProductResource is in Product module
use Illuminate\Http\Request;

// To use HTTP_CREATED for store method
// For flash sale not found

class FlashSaleController extends BaseController
{
    public function __construct(
        private readonly FlashSaleQueryService $flashSaleQueryService,
        private readonly FlashSaleWriteService $flashSaleWriteService
    ) {}

    public function index(Request $request)
    {
        $limit = (int) ($request->limit ?? 10);
        $flashSales = $this->flashSaleQueryService->getFlashSalesQuery($request)->paginate($limit);

        return FlashSaleResource::collection($flashSales);
    }

    public function store(FlashSaleCreateRequest $request)
    {
        $this->authorize('create', FlashSale::class);
        $data = FlashSaleData::fromRequest($request->validated());
        $flashSale = $this->flashSaleWriteService->createFlashSale($data);

        return new FlashSaleResource($flashSale);
    }

    public function show(Request $request, string $slug)
    {
        $language = $request->language ?? config('shop.default_language', 'id');
        $flashSale = $this->flashSaleQueryService->findFlashSaleBySlug($slug, $language);
        if (! $flashSale) {
            return $this->sendError('Flash sale not found', 404);
        }

        return new FlashSaleResource($flashSale);
    }

    public function update(FlashSaleUpdateRequest $request, int $id)
    {
        $flashSale = FlashSale::findOrFail($id);
        $this->authorize('update', $flashSale);
        $data = FlashSaleData::fromRequest($request->validated());
        $updated = $this->flashSaleWriteService->updateFlashSale($flashSale, $data);

        return new FlashSaleResource($updated);
    }

    public function destroy(Request $request, int $id)
    {
        $flashSale = FlashSale::findOrFail($id);
        $this->authorize('delete', $flashSale);
        $this->flashSaleWriteService->deleteFlashSale($flashSale);

        return $this->sendSuccess(null, 'Flash sale deleted successfully');
    }

    public function getProductsByFlashSale(Request $request)
    {
        $request->validate(['slug' => 'required|string']);
        $limit = (int) ($request->limit ?? 10);
        $language = $request->language ?? config('shop.default_language', 'id');
        $products = $this->flashSaleQueryService->getProductsByFlashSaleSlug($request->slug, $language, $limit);

        return ProductResource::collection($products);
    }

    public function getFlashSaleInfoByProductID(Request $request)
    {
        $request->validate(['id' => 'required|integer|exists:products,id']);
        $info = $this->flashSaleQueryService->getFlashSaleInfoByProductId($request->id);

        return response()->json($info);
    }
}
