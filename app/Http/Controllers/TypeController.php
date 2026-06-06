<?php

namespace App\Http\Controllers;

use App\Services\TypeService;
use App\Http\Requests\TypeRequest;
use App\Http\Resources\TypeResource;
use App\DTO\TypeData;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class TypeController extends Controller
{
    public function __construct(private TypeService $typeService) {}

    public function index(Request $request)
    {
        $language = $request->language ?? config('shop.default_language', 'en');
        $limit = $request->limit ?? 15;

        $types = $this->typeService->getTypesByLanguage($language, $limit);
        return TypeResource::collection($types);
    }

    public function store(TypeRequest $request)
    {
        $data = TypeData::fromRequest($request->validated());
        $type = $this->typeService->createType($data);

        return new TypeResource($type->load('banners'));
    }

    public function show(Request $request, $params)
    {
        $language = $request->language ?? config('shop.default_language', 'en');
        $type = $this->typeService->getTypeByIdOrSlug($params, $language);

        return new TypeResource($type);
    }

    public function update(TypeRequest $request, int $id)
    {
        $type = \App\Models\Type::findOrFail($id);
        $data = TypeData::fromRequest($request->validated());
        $updated = $this->typeService->updateType($type, $data);

        return new TypeResource($updated);
    }

    public function destroy(int $id): JsonResponse
    {
        $type = \App\Models\Type::findOrFail($id);
        $this->typeService->deleteType($type);

        return response()->json(['message' => 'Type deleted successfully']);
    }
}