<?php

namespace App\Http\Controllers;

use App\DTO\TypeData;
use App\Enums\Permission;
use App\Http\Requests\TypeRequest;
use App\Http\Resources\TypeResource;
use App\Models\Type;
use App\Services\TypeService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class TypeController extends BaseController
{
    public function __construct(private TypeService $typeService) {}

    public function index(Request $request)
    {
        $language = $request->language ?? config('shop.default_language', 'id');
        $limit = $request->limit ?? 15;
        $cacheKey = "types_{$language}_{$limit}";
        $types = Cache::remember($cacheKey, 3600, function () use ($language, $limit) {
            return $this->typeService->getTypesByLanguage($language, $limit);
        });

        return $this->sendPaginated(
            $types,
            TypeResource::collection($types->getCollection()),
            'Daftar type berhasil diambil.'
        );
    }

    public function store(TypeRequest $request)
    {
        $user = $request->user();
        if (! $user || ! $user->hasPermissionTo(Permission::SUPER_ADMIN->value)) {
            throw new AuthorizationException(config('notice.NOT_AUTHORIZED'));
        }
        $data = TypeData::fromRequest($request->validated());
        $type = $this->typeService->createType($data);
        Cache::forget("types_{$data->language}_*");

        return $this->sendSuccess(new TypeResource($type->load('banners')), 'Type created', 201);
    }

    public function show(Request $request, $params)
    {
        $language = $request->language ?? config('shop.default_language', 'id');
        $type = $this->typeService->getTypeByIdOrSlug($params, $language);

        return $this->sendSuccess(new TypeResource($type), 'Type detail');
    }

    public function update(TypeRequest $request, int $id)
    {
        $user = $request->user();
        if (! $user || ! $user->hasPermissionTo(Permission::SUPER_ADMIN->value)) {
            throw new AuthorizationException(config('notice.NOT_AUTHORIZED'));
        }
        $type = Type::findOrFail($id);
        $data = TypeData::fromRequest($request->validated());
        $updated = $this->typeService->updateType($type, $data);
        Cache::forget("types_{$data->language}_*");

        return $this->sendSuccess(new TypeResource($updated), 'Type updated');
    }

    public function destroy(Request $request, int $id)
    {
        $user = $request->user();
        if (! $user || ! $user->hasPermissionTo(Permission::SUPER_ADMIN->value)) {
            throw new AuthorizationException(config('notice.NOT_AUTHORIZED'));
        }
        $type = Type::findOrFail($id);
        $language = $type->language;
        $this->typeService->deleteType($type);
        Cache::forget("types_{$language}_*");

        return $this->sendSuccess(null, 'Type deleted');
    }
}
