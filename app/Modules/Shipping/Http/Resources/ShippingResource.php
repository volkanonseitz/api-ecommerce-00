<?php

namespace App\Modules\Shipping\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ShippingResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->resource->id,
            'name' => $this->resource->name,
            'amount' => $this->resource->amount,
            'is_global' => $this->resource->is_global,
            'type' => $this->resource->type,
            'created_at' => $this->resource->created_at,
            'updated_at' => $this->resource->updated_at,
        ];
    }
}
