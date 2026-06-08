<?php

namespace App\DTO;

class LanguageData
{
    public function __construct(
        public readonly string $language_name,
        public readonly string $language_code,
        public readonly string $flag,
    ) {}

    public static function fromRequest(array $data): self
    {
        return new self(
            language_name: $data['language_name'],
            language_code: $data['language_code'],
            flag: $data['flag'],
        );
    }
}