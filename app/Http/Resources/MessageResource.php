<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class MessageResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->resource->id,
            'conversation_id' => $this->resource->conversation_id,
            'user_id' => $this->resource->user_id,
            'user' => new UserResource($this->whenLoaded('user')),
            'body' => $this->resource->body,
            'created_at' => $this->resource->created_at,
            'updated_at' => $this->resource->updated_at,
        ];
    }
}
