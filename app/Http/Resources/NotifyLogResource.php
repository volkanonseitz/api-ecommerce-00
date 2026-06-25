<?php

namespace App\Http\Resources;

use App\Models\NotifyLog;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin NotifyLog
 */
class NotifyLogResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->resource->id,
            'receiver' => $this->resource->receiver,
            'sender' => $this->resource->sender,
            'notify_type' => $this->resource->notify_type,
            'notify_receiver_type' => $this->resource->notify_receiver_type,
            'is_read' => $this->resource->is_read,
            'notify_text' => $this->resource->notify_text,
            'notify_tracker' => $this->resource->notify_tracker,
            'created_at' => $this->resource->created_at,
            'deleted_at' => $this->resource->deleted_at,
            'sender_user' => new UserResource($this->whenLoaded('senderUser')),
        ];
    }
}
