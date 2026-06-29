<?php

declare(strict_types=1);

namespace App\Modules\Author\Http\Controllers;

use App\Http\Controllers\BaseController;
use App\Models\Author;
use App\Modules\Author\DTO\AuthorData;
use App\Modules\Author\Http\Requests\AuthorRequest;
use App\Modules\Author\Http\Resources\AuthorResource;
use App\Modules\Author\Services\AuthorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class AuthorController extends BaseController
{
    public function __construct(private AuthorService $authorService) {}

    public function index(Request $request)
    {
        $this->authorize('viewAny', Author::class);
        $limit = $request->limit ?? 15;
        $language = $request->language ?? config('shop.default_language', 'id');
        $cacheKey = "authors_{$language}_{$limit}";
        $authors = Cache::remember($cacheKey, 3600, function () use ($language, $limit) {
            return $this->authorService->getAuthorsByLanguage($language, $limit);
        });

        return $this->sendPaginated(
            $authors,
            AuthorResource::collection($authors->getCollection()),
            'Daftar author berhasil diambil.'
        );
    }

    public function store(AuthorRequest $request)
    {
        $this->authorize('create', Author::class);
        $data = AuthorData::fromRequest($request->validated());
        $author = $this->authorService->createAuthor($data);
        Cache::forget("authors_{$data->language}_*");

        return $this->sendSuccess(new AuthorResource($author), 'Author created', 201);
    }

    public function show(Request $request, string $slug)
    {
        $language = $request->language ?? config('shop.default_language', 'id');
        $author = $this->authorService->getAuthorBySlug($slug, $language);
        $this->authorize('view', $author);

        return $this->sendSuccess(new AuthorResource($author), 'Author detail');
    }

    public function update(AuthorRequest $request, int $id)
    {
        $author = Author::findOrFail($id);
        $this->authorize('update', $author);
        $data = AuthorData::fromRequest($request->validated());
        $updated = $this->authorService->updateAuthor($author, $data);
        Cache::forget("authors_{$data->language}_*");

        return $this->sendSuccess(new AuthorResource($updated), 'Author updated');
    }

    public function destroy(Request $request, int $id)
    {
        $author = Author::findOrFail($id);
        $this->authorize('delete', $author);
        $language = $author->language;
        $this->authorService->deleteAuthor($author);
        Cache::forget("authors_{$language}_*");

        return $this->sendSuccess(null, 'Author deleted');
    }

    public function topAuthor(Request $request)
    {
        $language = $request->language ?? config('shop.default_language', 'id');
        $limit = $request->limit ?? 10;
        $cacheKey = "top_authors_{$language}_{$limit}";
        $authors = Cache::remember($cacheKey, 3600, function () use ($language, $limit) {
            return $this->authorService->getTopAuthors($language, $limit);
        });

        return $this->sendSuccess(AuthorResource::collection($authors), 'Top authors');
    }
}
