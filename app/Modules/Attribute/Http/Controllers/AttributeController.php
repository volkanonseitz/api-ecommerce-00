<?php

declare(strict_types=1);

namespace App\Modules\Attribute\Http\Controllers;

use App\Http\Controllers\BaseController;
use App\Modules\Attribute\DTO\AttributeData;
use App\Modules\Attribute\Http\Requests\AttributeRequest;
use App\Modules\Attribute\Http\Resources\AttributeResource;
use App\Modules\Attribute\Services\AttributeQueryService;
use App\Modules\Attribute\Services\AttributeWriteService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class AttributeController extends BaseController
{
    public function __construct(
        private readonly AttributeQueryService $attributeQueryService,
        private readonly AttributeWriteService $attributeWriteService
    ) {}

    /**
     * GET /attributes - List attributes
     */
    public function index(Request $request)
    {
        $language = $request->language ?? config('shop.default_language', 'id');
        $cacheKey = "attributes_{$language}";
        $attributes = Cache::remember($cacheKey, 3600, function () use ($language) {
            return $this->attributeQueryService->getAttributesByLanguage($language);
        });

        return $this->sendSuccess(
            AttributeResource::collection($attributes),
            'Attributes retrieved'
        );
    }

    /**
     * POST /attributes - Create attribute
     */
    public function store(AttributeRequest $request)
    {
        $user = $request->user();
        $shopId = $request->shop_id;
        if (! $this->attributeQueryService->hasPermission($user, $shopId)) {
            throw new AuthorizationException(config('notice.NOT_AUTHORIZED'));
        }

        $data = AttributeData::fromRequest($request->validated());
        $attribute = $this->attributeWriteService->createAttribute($data);
        Cache::forget("attributes_{$data->language}");

        return $this->sendSuccess(
            new AttributeResource($attribute),
            'Attribute created',
            201
        );
    }

    /**
     * GET /attributes/{identifier} - Get attribute by ID or slug
     */
    public function show(Request $request, string $identifier)
    {
        $language = $request->language ?? config('shop.default_language', 'id');
        $attribute = $this->attributeQueryService->getAttributeByIdOrSlug($identifier, $language);

        return $this->sendSuccess(
            new AttributeResource($attribute),
            'Attribute detail'
        );
    }

    /**
     * PUT /attributes/{id} - Update attribute
     */
    public function update(AttributeRequest $request, int $id)
    {
        $user = $request->user();
        $shopId = $request->shop_id;
        if (! $this->attributeQueryService->hasPermission($user, $shopId)) {
            throw new AuthorizationException(config('notice.NOT_AUTHORIZED'));
        }

        $attribute = $this->attributeQueryService->getAttributeByIdOrSlug($id, $request->language ?? config('shop.default_language', 'id'));
        $data = AttributeData::fromRequest($request->validated());
        $updated = $this->attributeWriteService->updateAttribute($attribute, $data);
        Cache::forget("attributes_{$data->language}");

        return $this->sendSuccess(
            new AttributeResource($updated),
            'Attribute updated'
        );
    }

    /**
     * DELETE /attributes/{id} - Delete attribute
     */
    public function destroy(Request $request, int $id)
    {
        $attribute = $this->attributeQueryService->getAttributeByIdOrSlug($id, $request->language ?? config('shop.default_language', 'id'));
        $user = $request->user();
        $shopId = $attribute->shop_id;
        if (! $this->attributeQueryService->hasPermission($user, $shopId)) {
            throw new AuthorizationException(config('notice.NOT_AUTHORIZED'));
        }

        $language = $attribute->language;
        $this->attributeWriteService->deleteAttribute($attribute);
        Cache::forget("attributes_{$language}");

        return $this->sendSuccess(null, 'Attribute deleted');
    }

    /**
     * GET /attributes/export/{shopId} - Export attributes as CSV
     */
    public function exportAttributes(Request $request, int $shopId)
    {
        $user = $request->user();
        if (! $this->attributeQueryService->hasPermission($user, $shopId)) {
            throw new AuthorizationException(config('notice.NOT_AUTHORIZED'));
        }

        $list = $this->attributeQueryService->exportAttributes($shopId);
        $filename = 'attributes-for-shop-id-'.$shopId.'.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ];

        $callback = function () use ($list) {
            $handle = fopen('php://output', 'w');
            if (! empty($list)) {
                fputcsv($handle, array_keys($list[0]));
                foreach ($list as $row) {
                    fputcsv($handle, $row);
                }
            }
            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * POST /attributes/import - Import attributes from CSV
     */
    public function importAttributes(Request $request)
    {
        $user = $request->user();
        $shopId = $request->shop_id;
        if (! $this->attributeQueryService->hasPermission($user, $shopId)) {
            throw new AuthorizationException(config('notice.NOT_AUTHORIZED'));
        }

        $requestFile = $request->file('csv');
        if (! $requestFile) {
            return $this->sendError('CSV file is required', 422);
        }

        $path = $requestFile->store('csv-files', 'public');
        $fullPath = storage_path('app/public/'.$path);
        $data = $this->csvToArray($fullPath);

        if (empty($data)) {
            return $this->sendError('CSV file is empty or invalid', 422);
        }

        try {
            $this->attributeWriteService->importAttributes($data, $shopId, $user);
            Cache::forget('attributes_'.config('shop.default_language', 'id'));

            return $this->sendSuccess(null, 'Import successful');
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), 400);
        }
    }

    /**
     * Helper to convert CSV to array
     */
    private function csvToArray(string $filename, string $delimiter = ','): array
    {
        if (! file_exists($filename) || ! is_readable($filename)) {
            return [];
        }

        $header = null;
        $data = [];
        if (($handle = fopen($filename, 'r')) !== false) {
            while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
                if (! $header) {
                    $header = $row;
                } else {
                    if (count($header) === count($row)) {
                        $data[] = array_combine($header, $row);
                    }
                }
            }
            fclose($handle);
        }

        return $data;
    }
}
