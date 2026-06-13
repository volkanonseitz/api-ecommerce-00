<?php

namespace App\Http\Controllers;

use App\Services\TagService;
use App\Http\Requests\TagCreateRequest;
use App\Http\Requests\TagUpdateRequest;
use App\Http\Resources\TagResource;
use App\DTO\TagData;
use App\Models\Tag;
use Illuminate\Http\Request;

class TagController extends Controller
{
    public function __construct(private TagService $tagService) {}

    public function index(Request $request)
    {
        $language = $request->language ?? config('shop.default_language', 'id');
        $limit = $request->limit ?? 15;
        $tags = $this->tagService->getTags($language, $limit);
        $data = TagResource::collection($tags)->response()->getData(true);
        return formatAPIResourcePaginate($data);
    }

    public function store(TagCreateRequest $request)
    {
        $data = TagData::fromRequest($request->validated());
        $tag = $this->tagService->createTag($data);
        return new TagResource($tag);
    }

    public function show(Request $request, $params)
    {
        $language = $request->language ?? config('shop.default_language', 'id');
        $tag = $this->tagService->getTagByIdOrSlug($params, $language);
        return new TagResource($tag);
    }

    public function update(TagUpdateRequest $request, $id)
    {
        $tag = Tag::findOrFail($id);
        $data = TagData::fromRequest($request->validated());
        $updated = $this->tagService->updateTag($tag, $data);
        return new TagResource($updated);
    }

    public function destroy($id)
    {
        $tag = Tag::findOrFail($id);
        $this->tagService->deleteTag($tag);
        return response()->json(['message' => 'Tag deleted successfully']);
    }
}