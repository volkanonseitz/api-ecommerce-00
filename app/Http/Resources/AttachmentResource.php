<?php

namespace App\Http\Resources;

use App\Models\Attachment;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Attachment
 */
class AttachmentResource extends JsonResource
{
    public function toArray($request)
    {
        $media = $this->getMedia()->first();
        $isImage = $media ? strpos($media->mime_type, 'image/') !== false : false;

        return [
            'id' => $this->id,
            'thumbnail' => $media && $isImage ? $media->getUrl('thumbnail') : '',
            'original' => $media ? $media->getUrl() : null,
            'created_at' => $this->created_at,
        ];
    }
}
