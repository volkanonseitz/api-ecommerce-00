<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class AbusiveReportResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'model_id' => $this->model_id,
            'model_type' => $this->model_type,
            'message' => $this->message,
            'user_id' => $this->user_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}