<?php

declare(strict_types=1);

namespace App\Modules\Attachment\DTO;

use Illuminate\Http\UploadedFile;

final class AttachmentData
{
    /**
     * @param  array<int, UploadedFile>  $files
     */
    public function __construct(
        public readonly array $files,
    ) {}

    public static function fromRequest(array $data): self
    {
        return new self(
            files: $data['attachment'] ?? [],
        );
    }
}
