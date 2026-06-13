<?php

namespace App\Http\Controllers;

use App\Services\ResourceService;
use App\Http\Requests\ResourceCreateRequest;
use App\Http\Requests\ResourceUpdateRequest;
use App\Http\Resources\ResourceResource;
use App\DTO\ResourceData;
use App\Models\Resource;
use Illuminate\Http\Request;

class ResourceController extends Controller
{
    public function __construct(private ResourceService $resourceService) {}

    /**
     * GET /resources
     */
    public function index(Request $request)
    {
        $language = $request->language ?? config('shop.default_language', 'id');
        $limit = $request->limit ?? 15;
        $resources = $this->resourceService->getResources($language, $limit);
        return ResourceResource::collection($resources);
    }

    /**
     * POST /resources
     */
    public function store(ResourceCreateRequest $request)
    {
        $data = ResourceData::fromRequest($request->validated());
        $resource = $this->resourceService->create($data);
        return new ResourceResource($resource);
    }

    /**
     * GET /resources/{params}
     */
    public function show(Request $request, $params)
    {
        $language = $request->language ?? config('shop.default_language', 'id');
        $resource = $this->resourceService->find($params, $language);
        return new ResourceResource($resource);
    }

    /**
     * PUT /resources/{id}
     */
    public function update(ResourceUpdateRequest $request, $id)
    {
        $resource = Resource::findOrFail($id);
        $data = ResourceData::fromRequest($request->validated());
        $updated = $this->resourceService->update($resource, $data);
        return new ResourceResource($updated);
    }

    /**
     * DELETE /resources/{id}
     */
    public function destroy($id)
    {
        $resource = Resource::findOrFail($id);
        $this->resourceService->delete($resource);
        return response()->json(['message' => 'Resource deleted successfully']);
    }
}