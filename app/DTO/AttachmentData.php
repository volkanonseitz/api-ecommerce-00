<?php

namespace App\DTO;

class AttachmentData
{
    public function __construct(
        public readonly array $files, // uploaded file instances
    ) {}

    public static function fromRequest(array $data): self
    {
        return new self(
            files: $data['attachment'] ?? [],
        );
    }
}