<?php

declare(strict_types=1);

namespace App\Modules\Author\Services;

use App\Models\Author;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class AuthorService
{
    /**
     * @return LengthAwarePaginator<Author>
     */
    public function getAuthorsByLanguage(string $language, int $perPage = 15): LengthAwarePaginator
    {
        return Author::query()
            ->where('language', $language)
            ->withCount('products')
            ->paginate($perPage);
    }

    public function getAuthorBySlug(string $slug, string $language): Author
    {
        return Author::where('slug', $slug)
            ->where('language', $language)
            ->firstOrFail();
    }

    /**
     * @return Collection<int, Author>
     */
    public function getTopAuthors(string $language, int $limit = 10): Collection
    {
        return Author::query()
            ->where('language', $language)
            ->withCount('products')
            ->orderByDesc('products_count')
            ->limit($limit)
            ->get();
    }
}
