<?php

declare(strict_types=1);

namespace App\Modules\Tag\DTO;

use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TagData
{
    public function __construct(
        public readonly string $name,
        public readonly ?string $slug,
        public readonly ?int $typeId,
        public readonly ?string $icon,
        public readonly ?array $image,
        public readonly ?string $details,
        public readonly string $language,
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            name: $request->input('name'),
            slug: Str::slug($request->input('name')),
            typeId: $request->input('type_id'),
            icon: $request->input('icon'),
            image: $request->input('image'),
            details: $request->input('details'),
            language: $request->input('language', config('shop.default_language', 'id')),
        );
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'slug' => $this->slug,
            'type_id' => $this->typeId,
            'icon' => $this->icon,
            'image' => $this->image,
            'details' => $this->details,
            'language' => $this->language,
        ];
    }
}
