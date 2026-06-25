<?php

namespace App\Http\Controllers;

use App\DTO\ResourceData;
use App\Enums\Permission;
use App\Http\Requests\ResourceCreateRequest;
use App\Http\Requests\ResourceUpdateRequest;
use App\Http\Resources\ResourceResource;
use App\Models\Resource;
use App\Services\ResourceService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ResourceController extends BaseController
{
    public function __construct(private ResourceService $resourceService) {}

    public function index(Request $request)
    {
        $language = $request->language ?? config('shop.default_language', 'id');
        $limit = $request->limit ?? 15;
        $cacheKey = "resources_{$language}_{$limit}";
        $resources = Cache::remember($cacheKey, 3600, function () use ($language, $limit) {
            return $this->resourceService->getResources($language, $limit);
        });

        return $this->sendPaginated(
            $resources,
            ResourceResource::collection($resources->getCollection()),
            'Daftar resource berhasil diambil.'
        );
    }

    public function store(ResourceCreateRequest $request)
    {
        $user = $request->user();
        if (! $user || ! $user->hasPermissionTo(Permission::SUPER_ADMIN->value)) {
            throw new AuthorizationException(config('notice.NOT_AUTHORIZED'));
        }
        $data = ResourceData::fromRequest($request->validated());
        $resource = $this->resourceService->create($data);
        Cache::forget("resources_{$data->language}_*");

        return $this->sendSuccess(new ResourceResource($resource), 'Resource created', 201);
    }

    public function show(Request $request, string $params)
    {
        $language = $request->language ?? config('shop.default_language', 'id');
        $resource = $this->resourceService->find($params, $language);

        return $this->sendSuccess(new ResourceResource($resource), 'Resource detail');
    }

    public function update(ResourceUpdateRequest $request, $id)
    {
        $user = $request->user();
        if (! $user || ! $user->hasPermissionTo(Permission::SUPER_ADMIN->value)) {
            throw new AuthorizationException(config('notice.NOT_AUTHORIZED'));
        }
        $resource = Resource::findOrFail($id);
        $data = ResourceData::fromRequest($request->validated());
        $updated = $this->resourceService->update($resource, $data);
        Cache::forget("resources_{$data->language}_*");

        return $this->sendSuccess(new ResourceResource($updated), 'Resource updated');
    }

    public function destroy(Request $request, $id)
    {
        $user = $request->user();
        if (! $user || ! $user->hasPermissionTo(Permission::SUPER_ADMIN->value)) {
            throw new AuthorizationException(config('notice.NOT_AUTHORIZED'));
        }
        $resource = Resource::findOrFail($id);
        $language = $resource->language;
        $this->resourceService->delete($resource);
        Cache::forget("resources_{$language}_*");

        return $this->sendSuccess(null, 'Resource deleted');
    }
}
