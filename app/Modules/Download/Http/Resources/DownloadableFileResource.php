<?php

declare(strict_types=1);

namespace App\Modules\Download\Http\Resources;

use App\Models\OrderedFile;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin OrderedFile
 */
class DownloadableFileResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'purchase_key' => $this->purchase_key,
            'digital_file_id' => $this->digital_file_id,
            'customer_id' => $this->customer_id,
            'tracking_number' => $this->tracking_number,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
            'file' => $this->whenLoaded('file', function () {
                return [
                    'id' => $this->file?->id,
                    'attachment_id' => $this->file?->attachment_id,
                ];
            }),
            'order' => $this->whenLoaded('order', function () {
                return [
                    'tracking_number' => $this->order?->tracking_number,
                    'order_status' => $this->order?->order_status,
                ];
            }),
            // relasi morph ke product/variation via file.fileable
            'product' => $this->when($this->file && $this->file->fileable_type === 'App\\Models\\Product', function () {
                return [
                    'id' => $this->file->fileable_id,
                    'shop' => $this->file->fileable?->shop ?? null,
                ];
            }),
            'variation' => $this->when($this->file && $this->file->fileable_type === 'App\\Models\\Variation', function () {
                return [
                    'id' => $this->file->fileable_id,
                    'product' => $this->file->fileable?->product ?? null,
                ];
            }),
        ];
    }
}
