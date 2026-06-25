<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class DownloadableFileResource extends JsonResource
{
    public function toArray($request)
    {
        // Resource untuk OrderedFile
        return [
            'id' => $this->resource->id,
            'purchase_key' => $this->resource->purchase_key,
            'digital_file_id' => $this->resource->digital_file_id,
            'customer_id' => $this->resource->customer_id,
            'tracking_number' => $this->resource->tracking_number,
            'created_at' => $this->resource->created_at,
            'updated_at' => $this->resource->updated_at,
            'file' => $this->resource->whenLoaded('file', function () {
                return [
                    'id' => $this->resource->file->id,
                    'attachment_id' => $this->resource->file->attachment_id,
                    // jangan tampilkan url jika tidak perlu
                ];
            }),
            'order' => $this->resource->whenLoaded('order', function () {
                return [
                    'tracking_number' => $this->resource->order->tracking_number,
                    'order_status' => $this->resource->order->order_status,
                ];
            }),
            // relasi morph ke product/variation via file.fileable
            'product' => $this->resource->when($this->resource->file && $this->resource->file->fileable_type === 'App\\Models\\Product', function () {
                return [
                    'id' => $this->resource->file->fileable_id,
                    'shop' => $this->resource->file->fileable->shop ?? null,
                ];
            }),
            'variation' => $this->resource->when($this->resource->file && $this->resource->file->fileable_type === 'App\\Models\\Variation', function () {
                return [
                    'id' => $this->resource->file->fileable_id,
                    'product' => $this->resource->file->fileable->product ?? null,
                ];
            }),
        ];
    }
}
