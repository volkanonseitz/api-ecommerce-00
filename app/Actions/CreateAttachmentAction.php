<?php

namespace App\Actions;

use App\Models\Attachment;
use App\DTO\AttachmentData;

class CreateAttachmentAction
{
    /**
     * @return array<array{thumbnail: string, original: string, id: int}>
     */
    public function execute(AttachmentData $data): array
    {
        $results = [];
        foreach ($data->files as $file) {
            $attachment = Attachment::create();
            $attachment->addMedia($file)->toMediaCollection();
            $media = $attachment->getMedia()->first(); // ambil media yang baru ditambahkan
            if ($media) {
                $isImage = strpos($media->mime_type, 'image/') !== false;
                $results[] = [
                    'thumbnail' => $isImage ? $media->getUrl('thumbnail') : '',
                    'original' => $media->getUrl(),
                    'id' => $attachment->id,
                ];
            }
        }
        return $results;
    }
}