<?php

declare(strict_types=1);

namespace App\Modules\Type\Services;

use App\Models\Type;
use App\Modules\Type\Actions\CreateTypeAction;
use App\Modules\Type\Actions\DeleteTypeAction;
use App\Modules\Type\Actions\UpdateTypeAction;
use App\Modules\Type\DTO\TypeData;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;

class TypeService
{
    public function __construct(
        private CreateTypeAction $createType,
        private UpdateTypeAction $updateType,
        private DeleteTypeAction $deleteType
    ) {}

    public function getAllTypes(string $language, int $perPage = 15): LengthAwarePaginator
    {
        return Type::where('language', $language)
            ->with('banners')
            ->paginate($perPage);
    }

    public function getTypesWithCache(string $language, int $perPage = 15): LengthAwarePaginator
    {
        $cacheKey = $this->generateCacheKey($language, $perPage);
        
        return Cache::remember($cacheKey, 3600, function () use ($language, $perPage) {
            return $this->getAllTypes($language, $perPage);
        });
    }

    public function getTypeById(int $id): Type
    {
        return Type::with('banners')->findOrFail($id);
    }

    public function getTypeByIdentifier(string $identifier, string $language): Type
    {
        if (is_numeric($identifier)) {
            return $this->getTypeById((int) $identifier);
        }

        return Type::with('banners')
            ->where('slug', $identifier)
            ->where('language', $language)
            ->firstOrFail();
    }

    public function createType(TypeData $data): Type
    {
        $type = $this->createType->execute($data);
        $this->invalidateCache($data->language);
        return $type;
    }

    public function updateType(Type $type, TypeData $data): Type
    {
        $updatedType = $this->updateType->execute($type, $data);
        $this->invalidateCache($data->language);
        return $updatedType;
    }

    public function deleteType(Type $type): void
    {
        $language = $type->language;
        $this->deleteType->execute($type);
        $this->invalidateCache($language);
    }

    private function generateCacheKey(string $language, int $perPage): string
    {
        return "types:{$language}:{$perPage}";
    }

    private function invalidateCache(string $language): void
    {
        Cache::tags(["types:{$language}"])->flush();
    }
}