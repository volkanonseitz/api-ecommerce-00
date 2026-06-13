<?php

namespace App\DTO;

class SettingsData
{
    public function __construct(
        public readonly array $options,
        public readonly ?string $language,
    ) {}

    public static function fromRequest(array $data): self
    {
        return new self(
            options: $data['options'] ?? [],
            language: $data['language'] ?? config('shop.default_language', 'id'),
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