<?php

namespace App\Http\Resources;

use App\Enums\AbusiveReportTypes;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AbusiveReportResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,

            'target' => [
    'id' => $this->model_id,
    'type' => AbusiveReportType::fromModelClass(
        $this->model_type
    ),
],

            'message' => $this->message,

            'user_id' => $this->when(
                $request->user()?->can('manage-abusive-reports'),
                $this->user_id
            ),

            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}