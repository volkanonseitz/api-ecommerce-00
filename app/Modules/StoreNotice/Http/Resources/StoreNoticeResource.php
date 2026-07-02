<?php

namespace App\Modules\StoreNotice\Http\Resources;

use App\Modules\Shop\Http\Resources\ShopResource;
use App\Modules\User\Http\Resources\UserResource;
use Illuminate\Http\Resources\Json\JsonResource;

class StoreNoticeResource extends JsonResource
{
    public function toArray($request)
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
            'is_read' => $this->resource->is_read,
            'creator' => [
                'id' => $this->resource->creator->id,
                'name' => $this->resource->creator->name,
                'email' => $this->resource->creator->email,
            ],
            'users' => UserResource::collection($this->whenLoaded('users')),
            'shops' => ShopResource::collection($this->whenLoaded('shops')),
            'read_status' => $this->resource->readStatusCollection(),
        ];
    }

    private function readStatusCollection()
    {
        return $this->resource->read_status->map(function ($user) {
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
