<?php

namespace App\Http\Controllers;

use App\Services\AttributeService;
use App\Http\Requests\AttributeRequest;
use App\Http\Resources\AttributeResource;
use App\DTO\AttributeData;
use Illuminate\Http\Request;
use Illuminate\Auth\Access\AuthorizationException;
use App\Models\Attribute;

class AttributeController extends Controller
{
    public function __construct(private AttributeService $attributeService) {}

    /**
     * GET /attributes
     */
    public function index(Request $request)
    {
        $language = $request->language ?? config('shop.default_language', 'id');
        $attributes = $this->attributeService->getAttributesByLanguage($language);
        return AttributeResource::collection($attributes);
    }

    /**
     * POST /attributes
     */
    public function store(AttributeRequest $request)
    {
        $user = $request->user();
        $shopId = $request->shop_id;
        if (!$this->attributeService->hasPermission($user, $shopId)) {
            throw new AuthorizationException(config('notice.NOT_AUTHORIZED'));
        }
        $data = AttributeData::fromRequest($request->validated());
        $attribute = $this->attributeService->createAttribute($data);
        return new AttributeResource($attribute);
    }

    /**
     * GET /attributes/{identifier} (id or slug)
     */
    public function show(Request $request, $identifier)
    {
        $language = $request->language ?? config('shop.default_language', 'id');
        $attribute = $this->attributeService->getAttributeByIdOrSlug($identifier, $language);
        return new AttributeResource($attribute);
    }

    /**
     * PUT /attributes/{id}
     */
    public function update(AttributeRequest $request, $id)
    {
        $user = $request->user();
        $shopId = $request->shop_id;
        if (!$this->attributeService->hasPermission($user, $shopId)) {
            throw new AuthorizationException(config('notice.NOT_AUTHORIZED'));
        }
        $attribute = Attribute::findOrFail($id);
        $data = AttributeData::fromRequest($request->validated());
        $updated = $this->attributeService->updateAttribute($attribute, $data);
        return new AttributeResource($updated);
    }

    /**
     * DELETE /attributes/{id}
     */
    public function destroy(Request $request, $id)
    {
        $attribute = Attribute::findOrFail($id);
        $user = $request->user();
        $shopId = $attribute->shop_id;
        if (!$this->attributeService->hasPermission($user, $shopId)) {
            throw new AuthorizationException(config('notice.NOT_AUTHORIZED'));
        }
        $this->attributeService->deleteAttribute($attribute);
        return response()->json(['message' => 'Attribute deleted successfully']);
    }

    /**
     * GET /attributes/export/{shop_id}
     */
    public function exportAttributes(Request $request, $shopId)
    {
        $user = $request->user();
        if (!$this->attributeService->hasPermission($user, $shopId)) {
            throw new AuthorizationException(config('notice.NOT_AUTHORIZED'));
        }
        $list = $this->attributeService->exportAttributes($shopId);
        $filename = 'attributes-for-shop-id-' . $shopId . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];
        $callback = function () use ($list) {
            $handle = fopen('php://output', 'w');
            if (!empty($list)) {
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
     * POST /attributes/import
     */
    public function importAttributes(Request $request)
    {
        $user = $request->user();
        $shopId = $request->shop_id;
        if (!$this->attributeService->hasPermission($user, $shopId)) {
            throw new AuthorizationException(config('notice.NOT_AUTHORIZED'));
        }
        $requestFile = $request->file('csv');
        if (!$requestFile) {
            throw new \Exception("CSV_NOT_FOUND");
        }
        $path = $requestFile->store('csv-files', 'public');
        $fullPath = storage_path('app/public/' . $path);
        $data = $this->csvToArray($fullPath);
        $this->attributeService->importAttributes($data, $shopId, $user);
        return response()->json(['message' => 'Import successful']);
    }

    private function csvToArray($filename, $delimiter = ',')
    {
        if (!file_exists($filename)) return [];
        $header = null;
        $data = [];
        if (($handle = fopen($filename, 'r')) !== false) {
            while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
                if (!$header) {
                    $header = $row;
                } else {
                    $data[] = array_combine($header, $row);
                }
            }
            fclose($handle);
        }
        return $data;
    }
}