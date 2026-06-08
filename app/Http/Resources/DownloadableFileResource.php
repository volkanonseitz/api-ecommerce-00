<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class DownloadableFileResource extends JsonResource
{
    public function toArray($request)
    {
        // Resource untuk OrderedFile
        return [
            'id' => $this->id,
            'purchase_key' => $this->purchase_key,
            'digital_file_id' => $this->digital_file_id,
            'customer_id' => $this->customer_id,
            'tracking_number' => $this->tracking_number,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'file' => $this->whenLoaded('file', function() {
                return [
                    'id' => $this->file->id,
                    'attachment_id' => $this->file->attachment_id,
                    // jangan tampilkan url jika tidak perlu
                ];
            }),
            'order' => $this->whenLoaded('order', function() {
                return [
                    'tracking_number' => $this->order->tracking_number,
                    'order_status' => $this->order->order_status,
                ];
            }),
            // relasi morph ke product/variation via file.fileable
            'product' => $this->when($this->file && $this->file->fileable_type === 'App\\Models\\Product', function() {
                return [
                    'id' => $this->file->fileable_id,
                    'shop' => $this->file->fileable->shop ?? null,
                ];
            }),
            'variation' => $this->when($this->file && $this->file->fileable_type === 'App\\Models\\Variation', function() {
                return [
                    'id' => $this->file->fileable_id,
                    'product' => $this->file->fileable->product ?? null,
                ];
            }),
        ];
    }
}