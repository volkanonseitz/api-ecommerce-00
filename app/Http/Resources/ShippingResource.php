<?php

namespace App\Http\Resources;

use App\Models\Shipping;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Shipping
 */
class ShippingResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'amount' => $this->amount,
            'is_global' => $this->is_global,
            'type' => $this->type,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
