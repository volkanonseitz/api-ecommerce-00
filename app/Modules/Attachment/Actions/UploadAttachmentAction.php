<?php

declare(strict_types=1);

namespace App\Modules\Attachment\Actions;

use App\Models\Attachment;
use App\Modules\Attachment\DTO\AttachmentData;
use Illuminate\Http\UploadedFile;

class UploadAttachmentAction
{
    /**
     * @return array<int, array{id: int, original: string, thumbnail: string}>
     */
    public function execute(AttachmentData $data): array
    {
        $results = [];

        foreach ($data->files as $file) {
            if ($file instanceof UploadedFile) {
                $attachment = new Attachment;
                $attachment->save();

                // Upload ke media library
                $media = $attachment->addMedia($file)
                    ->usingName(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME))
                    ->toMediaCollection();

                // Dapatkan URL thumbnail dan original
                $isImage = strpos($media->mime_type, 'image/') !== false;
                $thumbnail = $isImage ? $media->getUrl('thumbnail') : '';
                $original = $media->getUrl();

                $results[] = [
                    'id' => $attachment->id,
                    'original' => $original,
                    'thumbnail' => $thumbnail,
                ];
            }
        }

        return $results;
    }
}
