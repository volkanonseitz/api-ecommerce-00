<?php

namespace App\Http\Controllers;

use App\Services\ManufacturerService;
use App\Http\Requests\ManufacturerRequest;
use App\Http\Resources\ManufacturerResource;
use App\DTO\ManufacturerData;
use App\Models\Manufacturer;
use App\Enums\Permission;
use Illuminate\Http\Request;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Cache;

class ManufacturerController extends BaseController
{
    public function __construct(private ManufacturerService $manufacturerService) {}

    /**
     * GET /manufacturers
     */
    public function index(Request $request)
    {
        $limit = $request->limit ?? 15;
        $language = $request->language ?? config('shop.default_language', 'id');
        
        $cacheKey = "manufacturers_{$language}_{$limit}";
        $manufacturers = Cache::remember($cacheKey, 3600, function () use ($language, $limit) {
            return $this->manufacturerService->getManufacturersByLanguage($language, $limit);
        });

        return $this->sendPaginated(
            $manufacturers,
            ManufacturerResource::collection($manufacturers->getCollection()),
            'Daftar manufakturer berhasil diambil.'
        );
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
        if ($user && $user->hasPermissionTo(Permission::SUPER_ADMIN->value)) {
            $validated['is_approved'] = true;
        } else {
            $validated['is_approved'] = false;
        }

        $data = ManufacturerData::fromRequest($validated);
        $manufacturer = $this->manufacturerService->createManufacturer($data);
        
        // Hapus cache untuk bahasa yang sama
        Cache::forget("manufacturers_{$data->language}_*");

        return $this->sendSuccess(
            new ManufacturerResource($manufacturer->load('type')),
            'Manufacturer created',
            201
        );
    }

    /**
     * GET /manufacturers/{slug}
     */
    public function show(Request $request, string $slug)
    {
        $language = $request->language ?? config('shop.default_language', 'id');
        
        try {
            $manufacturer = $this->manufacturerService->getManufacturerByIdOrSlug($slug, $language);
            return $this->sendSuccess(
                new ManufacturerResource($manufacturer),
                'Manufacturer detail'
            );
        } catch (ModelNotFoundException $e) {
            return $this->sendError('Manufacturer not found', 404);
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

        // Non-admin tidak boleh mengubah is_approved, kunci ke status lama
        if (!($user && $user->hasPermissionTo(Permission::SUPER_ADMIN->value))) {
            $validated['is_approved'] = $manufacturer->is_approved;
        }

        $data = ManufacturerData::fromRequest($validated);
        $updated = $this->manufacturerService->updateManufacturer($manufacturer, $data);
        
        // Hapus cache
        Cache::forget("manufacturers_{$data->language}_*");

        return $this->sendSuccess(
            new ManufacturerResource($updated),
            'Manufacturer updated'
        );
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
        $language = $manufacturer->language;
        $this->manufacturerService->deleteManufacturer($manufacturer);

        Cache::forget("manufacturers_{$language}_*");

        return $this->sendSuccess(null, 'Manufacturer deleted successfully');
    }

    /**
     * GET /manufacturers/top
     */
    public function topManufacturer(Request $request)
    {
        $limit = $request->limit ?? 10;
        $language = $request->language ?? config('shop.default_language', 'id');
        
        $cacheKey = "top_manufacturers_{$language}_{$limit}";
        $manufacturers = Cache::remember($cacheKey, 3600, function () use ($language, $limit) {
            return $this->manufacturerService->getTopManufacturers($language, $limit);
        });

        return $this->sendSuccess(
            ManufacturerResource::collection($manufacturers),
            'Top manufacturers'
        );
    }
}