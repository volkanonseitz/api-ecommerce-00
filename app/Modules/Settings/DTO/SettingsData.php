<?php

declare(strict_types=1);

namespace App\Modules\Settings\DTO;

use Illuminate\Http\Request;

class SettingsData
{
    public function __construct(
        public readonly array $options,
        public readonly string $language,
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            options: $request->input('options') ?? [],
            language: $request->input('language', config('shop.default_language', 'id')),
        );
    }

    public function toArray(): array
    {
        return [
            'options' => $this->options,
            'language' => $this->language,
        ];
    }
}
