<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class FlashSaleVendorResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->resource->id,
            'title' => $this->resource->title,
            'note' => $this->resource->note,
            'flash_sale_id' => $this->resource->flash_sale_id,
            'language' => $this->resource->language,
            'request_status' => $this->resource->request_status,
            'created_at' => $this->resource->created_at,
            'updated_at' => $this->resource->updated_at,
            'deleted_at' => $this->resource->deleted_at,
            'products' => ProductResource::collection($this->whenLoaded('products')),
        ];
    }
}
