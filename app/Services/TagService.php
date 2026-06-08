<?php

namespace App\Services;

use App\Models\Tag;
use App\DTO\TagData;
use App\Actions\CreateTagAction;
use App\Actions\UpdateTagAction;

class TagService
{
    public function __construct(
        private CreateTagAction $createTag,
        private UpdateTagAction $updateTag,
    ) {}

    public function getTags(string $language, int $perPage = 15)
    {
        return Tag::where('language', $language)->with('type')->paginate($perPage);
    }

    public function getTagByIdOrSlug($param, string $language): Tag
    {
        if (is_numeric($param)) {
            return Tag::where('id', $param)->with('type')->firstOrFail();
        }
        return Tag::where('slug', $param)->where('language', $language)->with('type')->firstOrFail();
    }

    public function createTag(TagData $data): Tag
    {
        return $this->createTag->execute($data);
    }

    public function updateTag(Tag $tag, TagData $data): Tag
    {
        return $this->updateTag->execute($tag, $data);
    }

    public function deleteTag(Tag $tag): void
    {
        $tag->delete();
    }
}