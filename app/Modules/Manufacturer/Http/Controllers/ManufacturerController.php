<?php

declare(strict_types=1);

namespace App\Modules\Manufacturer\Http\Controllers;

use App\Enums\Permission;
use App\Http\Controllers\BaseController;
use App\Models\Manufacturer;
use App\Modules\Manufacturer\DTO\ManufacturerData;
use App\Modules\Manufacturer\Http\Requests\ManufacturerRequest;
use App\Modules\Manufacturer\Http\Resources\ManufacturerResource;
use App\Modules\Manufacturer\Services\ManufacturerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ManufacturerController extends BaseController
{
    public function __construct(private ManufacturerService $manufacturerService) {}

    public function index(Request $request)
    {
        $this->authorize('viewAny', Manufacturer::class);
        $limit = (int) ($request->limit ?? 15);
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

    public function store(ManufacturerRequest $request)
    {
        $this->authorize('create', Manufacturer::class);
        $user = $request->user();
        $validated = $request->validated();

        if ($user && $user->hasPermissionTo(Permission::SUPER_ADMIN->value)) {
            $validated['is_approved'] = true;
        } else {
            $validated['is_approved'] = false;
        }

        $data = ManufacturerData::fromRequest($validated);
        $manufacturer = $this->manufacturerService->createManufacturer($data);
        Cache::forget("manufacturers_{$data->language}_*");

        return $this->sendSuccess(
            new ManufacturerResource($manufacturer->load('type')),
            'Manufacturer created',
            201
        );
    }

    public function show(Request $request, string $slug)
    {
        $language = $request->language ?? config('shop.default_language', 'id');
        $manufacturer = $this->manufacturerService->getManufacturerByIdOrSlug($slug, $language);
        $this->authorize('view', $manufacturer);

        return $this->sendSuccess(
            new ManufacturerResource($manufacturer),
            'Manufacturer detail'
        );
    }

    public function update(ManufacturerRequest $request, int $id)
    {
        $manufacturer = Manufacturer::findOrFail($id);
        $this->authorize('update', $manufacturer);

        $validated = $request->validated();
        $user = $request->user();

        // Non-admin tidak boleh mengubah is_approved
        if (! ($user && $user->hasPermissionTo(Permission::SUPER_ADMIN->value))) {
            $validated['is_approved'] = $manufacturer->is_approved;
        }

        $data = ManufacturerData::fromRequest($validated);
        $updated = $this->manufacturerService->updateManufacturer($manufacturer, $data);
        Cache::forget("manufacturers_{$data->language}_*");

        return $this->sendSuccess(
            new ManufacturerResource($updated),
            'Manufacturer updated'
        );
    }

    public function destroy(Request $request, int $id)
    {
        $manufacturer = Manufacturer::findOrFail($id);
        $this->authorize('delete', $manufacturer);
        $language = $manufacturer->language;
        $this->manufacturerService->deleteManufacturer($manufacturer);
        Cache::forget("manufacturers_{$language}_*");

        return $this->sendSuccess(null, 'Manufacturer deleted successfully');
    }

    public function topManufacturer(Request $request)
    {
        $limit = (int) ($request->limit ?? 10);
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
