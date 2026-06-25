<?php

namespace App\Http\Resources;

use App\Enums\AbusiveReportType;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AbusiveReportResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->id,

            'target' => [
                'id' => $this->resource->model_id,
                'type' => AbusiveReportType::fromModelClass(
                    $this->resource->model_type
                ),
            ],

            'message' => $this->resource->message,

            'user_id' => $this->resource->when(
                $request->user()?->can('manage-abusive-reports'),
                $this->resource->user_id
            ),

            'created_at' => $this->resource->created_at?->toISOString(),
            'updated_at' => $this->resource->updated_at?->toISOString(),
        ];
    }
}
