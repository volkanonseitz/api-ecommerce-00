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

class AuthorController extends Controller
{
    public function __construct(private AuthorService $authorService) {}

    /**
     * GET /authors
     */
    public function index(Request $request)
    {
        $limit = $request->limit ?? 15;
        $language = $request->language ?? config('shop.default_language', 'id');
        $authors = $this->authorService->getAuthorsByLanguage($language, $limit);
        $data = AuthorResource::collection($authors)->response()->getData(true);

        return formatAPIResourcePaginate($data);
    }

    /**
     * POST /authors
     */
    public function store(AuthorRequest $request)
    {
        $user = $request->user();
        $shopId = $request->shop_id; // asumsikan ada shop_id di request
        if (! $this->authorService->hasPermission($user, $shopId)) {
            throw new AuthorizationException(config('notice.NOT_AUTHORIZED'));
        }
        $data = AuthorData::fromRequest($request->validated());
        $author = $this->authorService->createAuthor($data);

        return new AuthorResource($author);
    }

    /**
     * GET /authors/{slug}
     */
    public function show(Request $request, string $slug)
    {
        $language = $request->language ?? config('shop.default_language', 'id');
        try {
            $author = $this->authorService->getAuthorBySlug($slug, $language);

            return new AuthorResource($author);
        } catch (ModelNotFoundException $e) {
            throw new ModelNotFoundException(config('notice.NOT_FOUND'));
        }
    }

    /**
     * PUT /authors/{id}
     */
    public function update(AuthorRequest $request, int $id)
    {
        $user = $request->user();
        $author = Author::findOrFail($id);

        if (
            ! $this->authorService->hasPermission(
                $user,
                $author->shop_id
            )
        ) {
            throw new AuthorizationException(
                config('notice.NOT_AUTHORIZED')
            );
        }

        $author = Author::findOrFail($id);
        $data = AuthorData::fromRequest($request->validated());
        $updated = $this->authorService->updateAuthor($author, $data);

        return new AuthorResource($updated);
    }

    /**
     * DELETE /authors/{id}
     */
    public function destroy(Request $request, int $id)
    {
        $user = $request->user();
        if (! $user || ! $user->hasPermissionTo(Permission::SUPER_ADMIN->value)) {
            throw new AuthorizationException(config('notice.NOT_AUTHORIZED'));
        }

        $author = Author::findOrFail($id);
        $this->authorService->deleteAuthor($author);

        return response()->json(['message' => 'Author deleted successfully']);
    }

    /**
     * GET /authors/top
     */
    public function topAuthor(Request $request)
    {
        $language = $request->language ?? config('shop.default_language', 'id');
        $limit = $request->limit ?? 10;
        $authors = $this->authorService->getTopAuthors($language, $limit);

        return AuthorResource::collection($authors);
    }
}
