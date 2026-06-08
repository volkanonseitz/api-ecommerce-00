<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class StoreNoticeResource extends JsonResource
{
    public function toArray($request)
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
            'is_read' => $this->is_read,
            'creator' => [
                'id' => $this->creator->id,
                'name' => $this->creator->name,
                'email' => $this->creator->email,
            ],
            'users' => UserResource::collection($this->whenLoaded('users')),
            'shops' => ShopResource::collection($this->whenLoaded('shops')),
            'read_status' => $this->readStatusCollection(),
        ];
    }

    private function readStatusCollection()
    {
        return $this->read_status->map(function ($user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'is_read' => $user->pivot->is_read,
                'pivot' => $user->pivot,
            ];
        });
    }
}