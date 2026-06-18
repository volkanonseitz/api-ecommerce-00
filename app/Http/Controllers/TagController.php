<?php

namespace App\Http\Controllers;

use App\DTO\TagData;
use App\Enums\Permission;
use App\Http\Requests\TagCreateRequest;
use App\Http\Requests\TagUpdateRequest;
use App\Http\Resources\TagResource;
use App\Models\Tag;
use App\Services\TagService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class TagController extends BaseController
{
    public function __construct(private TagService $tagService) {}

    public function index(Request $request)
    {
        $language = $request->language ?? config('shop.default_language', 'id');
        $limit = $request->limit ?? 15;
        $cacheKey = "tags_{$language}_{$limit}";
        $tags = Cache::remember($cacheKey, 3600, function () use ($language, $limit) {
            return $this->tagService->getTags($language, $limit);
        });

        return $this->sendPaginated(
            $tags,
            TagResource::collection($tags->getCollection()),
            'Daftar tag berhasil diambil.'
        );
    }

    public function store(TagCreateRequest $request)
    {
        $user = $request->user();
        if (! $user || ! $user->hasPermissionTo(Permission::SUPER_ADMIN->value)) {
            throw new AuthorizationException(config('notice.NOT_AUTHORIZED'));
        }
        $data = TagData::fromRequest($request->validated());
        $tag = $this->tagService->createTag($data);
        Cache::forget("tags_{$data->language}_*");

        return $this->sendSuccess(new TagResource($tag), 'Tag created', 201);
    }

    public function show(Request $request, $params)
    {
        $language = $request->language ?? config('shop.default_language', 'id');
        $tag = $this->tagService->getTagByIdOrSlug($params, $language);

        return $this->sendSuccess(new TagResource($tag), 'Tag detail');
    }

    public function update(TagUpdateRequest $request, $id)
    {
        $user = $request->user();
        if (! $user || ! $user->hasPermissionTo(Permission::SUPER_ADMIN->value)) {
            throw new AuthorizationException(config('notice.NOT_AUTHORIZED'));
        }
        $tag = Tag::findOrFail($id);
        $data = TagData::fromRequest($request->validated());
        $updated = $this->tagService->updateTag($tag, $data);
        Cache::forget("tags_{$data->language}_*");

        return $this->sendSuccess(new TagResource($updated), 'Tag updated');
    }

    public function destroy(Request $request, $id)
    {
        $user = $request->user();
        if (! $user || ! $user->hasPermissionTo(Permission::SUPER_ADMIN->value)) {
            throw new AuthorizationException(config('notice.NOT_AUTHORIZED'));
        }
        $tag = Tag::findOrFail($id);
        $language = $tag->language;
        $this->tagService->deleteTag($tag);
        Cache::forget("tags_{$language}_*");

        return $this->sendSuccess(null, 'Tag deleted');
    }
}
