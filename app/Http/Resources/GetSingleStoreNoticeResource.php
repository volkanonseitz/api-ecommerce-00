<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GetSingleStoreNoticeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->id,
            'type' => $this->resource->type,
            'priority' => $this->resource->priority,
            'notice' => $this->resource->notice,
            'description' => $this->resource->description,
            'effective_from' => $this->resource->effective_from,
            'expired_at' => $this->resource->expired_at,
            'creator_role' => $this->resource->creator_role,
            'users' => $this->resource->users,
            'shops' => $this->resource->shops,
        ];
    }
}
