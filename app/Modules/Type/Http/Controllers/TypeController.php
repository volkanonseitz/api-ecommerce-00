<?php

namespace App\Modules\Type\Http\Controllers;

use App\Http\Controllers\BaseController;
use App\Models\Type;
use App\Modules\Type\Actions\CreateTypeAction;
use App\Modules\Type\Actions\DeleteTypeAction;
use App\Modules\Type\Actions\UpdateTypeAction;
use App\Modules\Type\DTO\TypeData;
use App\Modules\Type\Http\Requests\TypeRequest;
use App\Modules\Type\Http\Resources\TypeResource;
use App\Modules\Type\Services\TypeQueryService;
use Illuminate\Http\Request;

class TypeController extends BaseController
{
    public function __construct(
        private readonly TypeQueryService $queryService,
        private readonly CreateTypeAction $createAction,
        private readonly UpdateTypeAction $updateAction,
        private readonly DeleteTypeAction $deleteAction,
    ) {}

    public function index(Request $request)
    {
        $this->authorize('viewAny', Type::class);

        $language = $request->language ?? config('shop.default_language', 'id');
        $limit = $request->limit ?? 15;
        $types = $this->queryService->getTypesByLanguage($language, $limit);

        return $this->sendPaginated(
            $types,
            TypeResource::collection($types->getCollection()),
            'Daftar type berhasil diambil.'
        );
    }

    public function store(TypeRequest $request)
    {
        $this->authorize('create', Type::class);

        $data = TypeData::fromRequest($request->validated());
        $type = $this->createAction->execute($data);

        return $this->sendSuccess(new TypeResource($type->load('banners')), 'Type created', 201);
    }

    public function show(Request $request, string $params)
    {
        $language = $request->language ?? config('shop.default_language', 'id');
        $type = $this->queryService->getTypeByIdOrSlug($params, $language);
        $this->authorize('view', $type);

        return $this->sendSuccess(new TypeResource($type), 'Type detail');
    }

    public function update(TypeRequest $request, int $id)
    {
        $type = $this->queryService->findOrFail($id);
        $this->authorize('update', $type);

        $data = TypeData::fromRequest($request->validated());
        $updated = $this->updateAction->execute($type, $data);

        return $this->sendSuccess(new TypeResource($updated), 'Type updated');
    }

    public function destroy(Request $request, int $id)
    {
        $type = $this->queryService->findOrFail($id);
        $this->authorize('delete', $type);

        $this->deleteAction->execute($type);

        return $this->sendSuccess(null, 'Type deleted');
    }
}
