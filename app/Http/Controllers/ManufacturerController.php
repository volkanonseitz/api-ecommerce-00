<?php

namespace App\Http\Controllers;

use App\Services\ManufacturerService;
use App\Http\Requests\ManufacturerRequest;
use App\Http\Resources\ManufacturerResource;
use App\DTO\ManufacturerData;
use Illuminate\Http\Request;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Models\Manufacturer;

class ManufacturerController extends Controller
{
    public function __construct(private ManufacturerService $manufacturerService) {}

    /**
     * GET /manufacturers
     */
    public function index(Request $request)
    {
        $limit = $request->limit ?? 15;
        $language = $request->language ?? config('shop.default_language', 'en');
        
        $manufacturers = $this->manufacturerService->getManufacturersByLanguage($language, $limit);
        $data = ManufacturerResource::collection($manufacturers)->response()->getData(true);
        
        return formatAPIResourcePaginate($data);
    }

    /**
     * POST /manufacturers
     */
    public function store(ManufacturerRequest $request)
    {
        $user = $request->user();
        $shopId = $request->shop_id;

        if (!$this->manufacturerService->hasPermission($user, $shopId)) {
            throw new AuthorizationException(config('notice.NOT_AUTHORIZED'));
        }

        $validated = $request->validated();
        
        // Atur status persetujuan berdasarkan hak akses user
        if ($user && $user->hasPermissionTo(\App\Enums\Permission::SUPER_ADMIN->value)) {
            $validated['is_approved'] = true;
        } else {
            $validated['is_approved'] = false;
        }

        $data = ManufacturerData::fromRequest($validated);
        $manufacturer = $this->manufacturerService->createManufacturer($data);
        
        return new ManufacturerResource($manufacturer->load('type'));
    }

    /**
     * GET /manufacturers/{slug}
     */
    public function show(Request $request, string $slug)
    {
        $language = $request->language ?? config('shop.default_language', 'en');
        try {
            $manufacturer = $this->manufacturerService->getManufacturerByIdOrSlug($slug, $language);
            return new ManufacturerResource($manufacturer);
        } catch (ModelNotFoundException $e) {
            throw new ModelNotFoundException(config('notice.NOT_FOUND'));
        }
    }

    /**
     * PUT /manufacturers/{id}
     */
    public function update(ManufacturerRequest $request, int $id)
    {
        $user = $request->user();
        $shopId = $request->shop_id;

        if (!$this->manufacturerService->hasPermission($user, $shopId)) {
            throw new AuthorizationException(config('notice.NOT_AUTHORIZED'));
        }

        $manufacturer = Manufacturer::findOrFail($id);
        $validated = $request->validated();

        // Non-admin tidak boleh mengubah is_approved, kunci ke status lama jika bukan admin
        if (!($user && $user->hasPermissionTo(\App\Enums\Permission::SUPER_ADMIN->value))) {
            $validated['is_approved'] = $manufacturer->is_approved;
        }

        $data = ManufacturerData::fromRequest($validated);
        $updated = $this->manufacturerService->updateManufacturer($manufacturer, $data);
        
        return new ManufacturerResource($updated);
    }

    /**
     * DELETE /manufacturers/{id}
     */
    public function destroy(Request $request, int $id)
    {
        $user = $request->user();
        $shopId = $request->shop_id;

        if (!$this->manufacturerService->hasPermission($user, $shopId)) {
            throw new AuthorizationException(config('notice.NOT_AUTHORIZED'));
        }

        $manufacturer = Manufacturer::findOrFail($id);
        $this->manufacturerService->deleteManufacturer($manufacturer);
        
        return response()->json(['message' => 'Manufacturer deleted successfully']);
    }

    /**
     * GET /manufacturers/top
     */
    public function topManufacturer(Request $request)
    {
        $limit = $request->limit ?? 10;
        $language = $request->language ?? config('shop.default_language', 'en');
        
        $manufacturers = $this->manufacturerService->getTopManufacturers($language, $limit);
        return ManufacturerResource::collection($manufacturers);
    }
}