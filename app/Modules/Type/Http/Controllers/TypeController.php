<?php

declare(strict_types=1);

namespace App\Modules\Type\Http\Controllers;

use App\Http\Controllers\BaseController;
use App\Modules\Type\DTO\TypeData;
use App\Modules\Type\Http\Requests\TypeRequest;
use App\Modules\Type\Http\Resources\TypeResource;
use App\Modules\Type\Services\TypeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TypeController extends BaseController
{
    public function __construct(
        private TypeService $typeService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $language = $request->get('language', config('shop.default_language', 'id'));
        $perPage = (int) $request->get('limit', 15);
        
        $types = $this->typeService->getTypesWithCache($language, $perPage);

        return $this->sendPaginated(
            $types,
            TypeResource::collection($types->getCollection()),
            'Types retrieved successfully'
        );
    }

    public function store(TypeRequest $request): JsonResponse
    {
        $this->authorize('create', \App\Models\Type::class);
        
        $data = TypeData::fromRequest($request);
        $type = $this->typeService->createType($data);

        return $this->sendSuccess(
            new TypeResource($type->load('banners')),
            'Type created successfully',
            201
        );
    }

    public function show(Request $request, string $identifier): JsonResponse
    {
        $language = $request->get('language', config('shop.default_language', 'id'));
        $type = $this->typeService->getTypeByIdentifier($identifier, $language);
        
        $this->authorize('view', $type);

        return $this->sendSuccess(
            new TypeResource($type),
            'Type retrieved successfully'
        );
    }

    public function update(TypeRequest $request, int $id): JsonResponse
    {
        $type = $this->typeService->getTypeById($id);
        $this->authorize('update', $type);
        
        $data = TypeData::fromRequest($request);
        $updatedType = $this->typeService->updateType($type, $data);

        return $this->sendSuccess(
            new TypeResource($updatedType),
            'Type updated successfully'
        );
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $type = $this->typeService->getTypeById($id);
        $this->authorize('delete', $type);
        
        $this->typeService->deleteType($type);

        return $this->sendSuccess(
            null,
            'Type deleted successfully'
        );
    }
}