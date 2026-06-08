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
            'id' => $this->id,
            'type' => $this->type,
            'priority' => $this->priority,
            'notice' => $this->notice,
            'description' => $this->description,
            'effective_from' => $this->effective_from,
            'expired_at' => $this->expired_at,
            'creator_role' => $this->creator_role,
            'users' => $this->users,
            'shops' => $this->shops,
        ];
    }
}
