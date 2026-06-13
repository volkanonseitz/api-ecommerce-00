<?php

namespace App\Http\Controllers;

use App\Services\FlashSaleService;
use App\Http\Requests\CreateFlashSaleRequest;
use App\Http\Requests\UpdateFlashSaleRequest;
use App\Http\Resources\FlashSaleResource;
use App\DTO\FlashSaleData;
use App\Events\FlashSaleProcessed;
use App\Models\FlashSale;
use Illuminate\Http\Request;
use Illuminate\Auth\Access\AuthorizationException;
use App\Http\Resources\ProductResource;

class FlashSaleController extends Controller
{
    public function __construct(private FlashSaleService $flashSaleService) {}

    /**
     * GET /flash-sales
     */
    public function index(Request $request)
    {
        $limit = $request->limit ?? 10;
        $language = $request->language ?? config('shop.default_language', 'id');
        event(new FlashSaleProcessed('index', $language));
        $flashSales = $this->flashSaleService->getFlashSalesQuery($request)->paginate($limit);
        return FlashSaleResource::collection($flashSales);
    }

    /**
     * POST /flash-sales
     */
    public function store(CreateFlashSaleRequest $request)
    {
        if (!$this->flashSaleService->hasPermission($request->user())) {
            throw new AuthorizationException(config('notice.NOT_AUTHORIZED'));
        }
        $data = FlashSaleData::fromRequest($request->validated());
        $flashSale = $this->flashSaleService->createFlashSale($data);
        return new FlashSaleResource($flashSale);
    }

    /**
     * GET /flash-sales/{slug}
     */
    public function show(Request $request, $slug)
    {
        $language = $request->language ?? config('shop.default_language', 'id');
        $flashSale = $this->flashSaleService->findFlashSaleBySlug($slug, $language);
        if (!$flashSale) {
            abort(404, config('notice.NOT_FOUND'));
        }
        return new FlashSaleResource($flashSale);
    }

    /**
     * PUT /flash-sales/{id}
     */
    public function update(UpdateFlashSaleRequest $request, $id)
    {
        if (!$this->flashSaleService->hasPermission($request->user())) {
            throw new AuthorizationException(config('notice.NOT_AUTHORIZED'));
        }
        $flashSale = FlashSale::findOrFail($id);
        $data = FlashSaleData::fromRequest($request->validated());
        $updated = $this->flashSaleService->updateFlashSale($flashSale, $data);
        return new FlashSaleResource($updated);
    }

    /**
     * DELETE /flash-sales/{id}
     */
    public function destroy(Request $request, $id)
    {
        if (!$this->flashSaleService->hasPermission($request->user())) {
            throw new AuthorizationException(config('notice.NOT_AUTHORIZED'));
        }
        $flashSale = FlashSale::findOrFail($id);
        $this->flashSaleService->deleteFlashSale($flashSale);
        return response()->json(['message' => 'Flash sale deleted successfully']);
    }

    /**
     * GET /flash-sales/products/by-flash-sale
     */
    public function getProductsByFlashSale(Request $request)
    {
        $request->validate(['slug' => 'required|string']);
        $limit = $request->limit ?? 10;
        $language = $request->language ?? config('shop.default_language', 'id');
        $products = $this->flashSaleService->getProductsByFlashSaleSlug($request->slug, $language, $limit);
        return ProductResource::collection($products);
    }

    /**
     * GET /flash-sales/product-info
     */
    public function getFlashSaleInfoByProductID(Request $request)
    {
        $request->validate(['id' => 'required|integer|exists:products,id']);
        $info = $this->flashSaleService->getFlashSaleInfoByProductId($request->id);
        return response()->json($info);
    }
}