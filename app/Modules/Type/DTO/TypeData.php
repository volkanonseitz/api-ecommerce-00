<?php

declare(strict_types=1);

namespace App\Modules\Type\DTO;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class TypeData
{
    public function __construct(
        public string $name,
        public string $slug,
        public string $language,
        public ?array $promotionalSliders = null,
        public ?array $images = null,
        public ?array $settings = null,
        public ?string $icon = null,
        public ?string $description = null
    ) {}

    public static function fromRequest(Request $request): self
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:types,slug',
            'language' => 'required|string|size:2',
            'promotional_sliders' => 'nullable|array',
            'images' => 'nullable|array',
            'settings' => 'nullable|array',
            'icon' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:1000',
        ]);

        return new self(
            name: $validated['name'],
            slug: $validated['slug'],
            language: $validated['language'],
            promotionalSliders: $validated['promotional_sliders'] ?? null,
            images: $validated['images'] ?? null,
            settings: $validated['settings'] ?? null,
            icon: $validated['icon'] ?? null,
            description: $validated['description'] ?? null
        );
    }

    public static function fromArray(array $data): self
    {
        $validator = Validator::make($data, [
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255',
            'language' => 'required|string|size:2',
            'promotional_sliders' => 'nullable|array',
            'images' => 'nullable|array',
            'settings' => 'nullable|array',
            'icon' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return new self(
            name: $data['name'],
            slug: $data['slug'],
            language: $data['language'],
            promotionalSliders: $data['promotional_sliders'] ?? null,
            images: $data['images'] ?? null,
            settings: $data['settings'] ?? null,
            icon: $data['icon'] ?? null,
            description: $data['description'] ?? null
        );
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'slug' => $this->slug,
            'language' => $this->language,
            'promotional_sliders' => $this->promotionalSliders,
            'images' => $this->images,
            'settings' => $this->settings,
            'icon' => $this->icon,
            'description' => $this->description,
        ];
    }
}