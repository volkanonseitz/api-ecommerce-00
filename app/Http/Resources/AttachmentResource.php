<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class AttachmentResource extends JsonResource
{
    public function toArray($request)
    {
        $media = $this->resource->getMedia()->first();
        $isImage = $media ? strpos($media->mime_type, 'image/') !== false : false;

        return [
            'id' => $this->resource->id,
            'thumbnail' => $media && $isImage ? $media->getUrl('thumbnail') : '',
            'original' => $media ? $media->getUrl() : null,
            'created_at' => $this->resource->created_at,
        ];
    }
}
