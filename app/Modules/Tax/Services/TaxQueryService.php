<?php

declare(strict_types=1);

namespace App\Modules\Tax\Services;

use App\Models\Tax;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;

final class TaxQueryService
{
    private const CACHE_KEY_ALL = 'taxes_all';

    /**
     * @return Collection<int, Tax>
     */
    public function getAll(): Collection
    {
        return Cache::rememberForever(self::CACHE_KEY_ALL, function () {
            return Tax::all();
        });
    }

    public function find(int $id): Tax
    {
        return Tax::findOrFail($id);
    }

    public function findOrFail(int $id): Tax
    {
        return Tax::findOrFail($id);
    }
}
