<?php

namespace App\Services;

use App\Models\Resource;
use App\DTO\ResourceData;
use Illuminate\Support\Str;

class ResourceService
{
    public function getResources(string $language, int $perPage = 15)
    {
        return Resource::where('language', $language)->paginate($perPage);
    }

    public function find($params, string $language): Resource
    {
        if (is_numeric($params)) {
            return Resource::where('id', $params)->firstOrFail();
        }
        return Resource::where('slug', $params)->where('language', $language)->firstOrFail();
    }

    public function create(ResourceData $data): Resource
    {
        $attributes = array_filter([
            'name' => $data->name,
            'slug' => $data->slug ?? Str::slug($data->name),
            'type' => $data->type,
            'price' => $data->price,
            'image' => $data->image,
            'icon' => $data->icon,
            'details' => $data->details,
            'language' => $data->language,
            'is_approved' => $data->is_approved ?? false,
        ], fn($v) => !is_null($v));

        return Resource::create($attributes);
    }

    public function update(Resource $resource, ResourceData $data): Resource
    {
        $attributes = array_filter([
            'name' => $data->name,
            'slug' => ($data->slug && $data->slug !== $resource->slug) ? $data->slug : ($data->name ? Str::slug($data->name) : null),
            'type' => $data->type,
            'price' => $data->price,
            'image' => $data->image,
            'icon' => $data->icon,
            'details' => $data->details,
            'language' => $data->language,
            'is_approved' => $data->is_approved,
        ], fn($v) => !is_null($v));

        $resource->update($attributes);
        return $resource->fresh();
    }

    public function delete(Resource $resource): void
    {
        $resource->delete();
    }
}