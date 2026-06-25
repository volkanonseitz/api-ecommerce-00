<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class FeedbackResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->resource->id,
            'model_id' => $this->resource->model_id,
            'model_type' => $this->resource->model_type,
            'positive' => $this->resource->positive,
            'negative' => $this->resource->negative,
            'user_id' => $this->resource->user_id,
            'user' => $this->resource->whenLoaded('user', fn () => [
                'id' => $this->resource->user->id,
                'name' => $this->resource->user->name,
                'email' => $this->resource->user->email,
            ]),
            'created_at' => $this->resource->created_at,
            'updated_at' => $this->resource->updated_at,
        ];
    }
}
