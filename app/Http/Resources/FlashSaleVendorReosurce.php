<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class FlashSaleVendorResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'note' => $this->note,
            'flash_sale_id' => $this->flash_sale_id,
            'language' => $this->language,
            'request_status' => $this->request_status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,
            'products' => ProductResource::collection($this->whenLoaded('products')),
        ];
    }
}