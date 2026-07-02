<?php

declare(strict_types=1);

namespace App\Modules\Tag\Http\Controllers;

use App\Http\Controllers\BaseController;
use App\Models\Tag;
use App\Modules\Tag\DTO\TagData;
use App\Modules\Tag\Http\Requests\TagCreateRequest;
use App\Modules\Tag\Http\Requests\TagUpdateRequest;
use App\Modules\Tag\Http\Resources\TagResource;
use App\Modules\Tag\Services\TagService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TagController extends BaseController
{
    public function __construct(private TagService $tagService) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Tag::class);

        $language = $request->get('language', config('shop.default_language', 'id'));
        $limit = (int) $request->get('limit', 15);
        $tags = $this->tagService->getTags($language, $limit);

        return $this->sendPaginated(
            $tags,
            TagResource::collection($tags->getCollection()),
            'Tags retrieved successfully'
        );
    }

    public function store(TagCreateRequest $request): JsonResponse
    {
        $this->authorize('create', Tag::class);

        $data = TagData::fromRequest($request);
        $tag = $this->tagService->createTag($data, $request->user());

        return $this->sendSuccess(
            new TagResource($tag),
            'Tag created successfully',
            201
        );
    }

    public function show(Request $request, string $param): JsonResponse
    {
        $language = $request->get('language', config('shop.default_language', 'id'));
        $tag = $this->tagService->getTagByIdOrSlug($param, $language);

        $this->authorize('view', $tag);

        return $this->sendSuccess(
            new TagResource($tag),
            'Tag retrieved successfully'
        );
    }

    public function update(TagUpdateRequest $request, int $id): JsonResponse
    {
        $tag = Tag::findOrFail($id);
        $this->authorize('update', $tag);

        $data = TagData::fromRequest($request);
        $updated = $this->tagService->updateTag($tag, $data, $request->user());

        return $this->sendSuccess(
            new TagResource($updated),
            'Tag updated successfully'
        );
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $tag = Tag::findOrFail($id);
        $this->authorize('delete', $tag);

        $this->tagService->deleteTag($tag, $request->user());

        return $this->sendSuccess(
            null,
            'Tag deleted successfully'
        );
    }
}
