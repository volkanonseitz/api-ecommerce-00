<?php

namespace App\Http\Controllers;

use App\DTO\AuthorData;
use App\Enums\Permission;
use App\Http\Requests\AuthorRequest;
use App\Http\Resources\AuthorResource;
use App\Models\Author;
use App\Services\AuthorService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class AuthorController extends BaseController
{
    public function __construct(private AuthorService $authorService) {}

    public function index(Request $request)
    {
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
        $user = $request->user();
        $shopId = $request->shop_id;
        if (! $this->authorService->hasPermission($user, $shopId)) {
            throw new AuthorizationException(config('notice.NOT_AUTHORIZED'));
        }
        $data = AuthorData::fromRequest($request->validated());
        $author = $this->authorService->createAuthor($data);
        Cache::forget("authors_{$data->language}_*");

        return $this->sendSuccess(new AuthorResource($author), 'Author created', 201);
    }

    public function show(Request $request, string $slug)
    {
        $language = $request->language ?? config('shop.default_language', 'id');
        try {
            $author = $this->authorService->getAuthorBySlug($slug, $language);

            return $this->sendSuccess(new AuthorResource($author), 'Author detail');
        } catch (ModelNotFoundException $e) {
            return $this->sendError('Author not found', 404);
        }
    }

    public function update(AuthorRequest $request, int $id)
    {
        $user = $request->user();
        $author = Author::findOrFail($id);
        if (! $this->authorService->hasPermission($user, $author->shop_id)) {
            throw new AuthorizationException(config('notice.NOT_AUTHORIZED'));
        }
        $data = AuthorData::fromRequest($request->validated());
        $updated = $this->authorService->updateAuthor($author, $data);
        Cache::forget("authors_{$data->language}_*");

        return $this->sendSuccess(new AuthorResource($updated), 'Author updated');
    }

    public function destroy(Request $request, int $id)
    {
        $user = $request->user();
        if (! $user || ! $user->hasPermissionTo(Permission::SUPER_ADMIN->value)) {
            throw new AuthorizationException(config('notice.NOT_AUTHORIZED'));
        }
        $author = Author::findOrFail($id);
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
