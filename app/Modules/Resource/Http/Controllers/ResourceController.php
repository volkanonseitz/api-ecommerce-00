<?php

namespace App\Modules\Resource\Http\Controllers;

use App\Http\Controllers\BaseController;
use App\Models\Resource;
use App\Modules\Resource\Actions\CreateResourceAction;
use App\Modules\Resource\Actions\DeleteResourceAction;
use App\Modules\Resource\Actions\UpdateResourceAction;
use App\Modules\Resource\DTO\ResourceData;
use App\Modules\Resource\Http\Requests\ResourceCreateRequest;
use App\Modules\Resource\Http\Requests\ResourceUpdateRequest;
use App\Modules\Resource\Http\Resources\ResourceResource;
use App\Modules\Resource\Services\ResourceQueryService;
use Illuminate\Http\Request;

class ResourceController extends BaseController
{
    public function __construct(
        private readonly ResourceQueryService $queryService,
        private readonly CreateResourceAction $createAction,
        private readonly UpdateResourceAction $updateAction,
        private readonly DeleteResourceAction $deleteAction,
    ) {}

    public function index(Request $request)
    {
        $language = $request->language ?? config('shop.default_language', 'id');
        $limit = $request->limit ?? 15;
        $resources = $this->queryService->getResources($language, $limit);

        return $this->sendPaginated(
            $resources,
            ResourceResource::collection($resources->getCollection()),
            'Daftar resource berhasil diambil.'
        );
    }

    public function store(ResourceCreateRequest $request)
    {
        $this->authorize('create', Resource::class);

        $data = ResourceData::fromRequest($request->validated());
        $resource = $this->createAction->execute($data);

        return $this->sendSuccess(new ResourceResource($resource), 'Resource created', 201);
    }

    public function show(Request $request, string $params)
    {
        $language = $request->language ?? config('shop.default_language', 'id');
        $resource = $this->queryService->find($params, $language);

        return $this->sendSuccess(new ResourceResource($resource), 'Resource detail');
    }

    public function update(ResourceUpdateRequest $request, $id)
    {
        $resource = $this->queryService->findOrFail((int) $id);
        $this->authorize('update', $resource);

        $data = ResourceData::fromRequest($request->validated());
        $updated = $this->updateAction->execute($resource, $data);

        return $this->sendSuccess(new ResourceResource($updated), 'Resource updated');
    }

    public function destroy(Request $request, $id)
    {
        $resource = $this->queryService->findOrFail((int) $id);
        $this->authorize('delete', $resource);

        $this->deleteAction->execute($resource);

        return $this->sendSuccess(null, 'Resource deleted');
    }
}
