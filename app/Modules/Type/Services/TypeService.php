<?php

namespace App\Modules\Type\Services;

use App\Models\Type;
use App\Modules\Type\Actions\CreateTypeAction;
use App\Modules\Type\Actions\UpdateTypeAction;
use App\Modules\Type\DTO\TypeData;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class TypeService
{
    public function __construct(
        private CreateTypeAction $createType,
        private UpdateTypeAction $updateType
    ) {}

    public function getTypesByLanguage(string $language, int $limit): LengthAwarePaginator
    {
        return Type::where('language', $language)->paginate($limit);
    }

    public function getTypeByIdOrSlug($identifier, string $language): Type
    {
        if (is_numeric($identifier)) {
            return Type::with('banners')->findOrFail($identifier);
        }

        return Type::with('banners')
            ->where('slug', $identifier)
            ->where('language', $language)
            ->firstOrFail();
    }

    public function createType(TypeData $data): Type
    {
        return $this->createType->execute($data);
    }

    public function updateType(Type $type, TypeData $data): Type
    {
        return $this->updateType->execute($type, $data);
    }

    public function deleteType(Type $type): void
    {
        $type->banners()->delete();
        $type->delete();
    }
}
