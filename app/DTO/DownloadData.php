<?php

namespace App\DTO;

class DownloadData
{
    public function __construct(
        public readonly int $digital_file_id,
        public readonly int $user_id,
    ) {}

    public static function fromRequest(array $data, int $userId): self
    {
        return new self(
            digital_file_id: $data['digital_file_id'],
            user_id: $userId,
        );
    }
}