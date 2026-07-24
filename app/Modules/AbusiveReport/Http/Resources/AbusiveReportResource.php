<?php

declare(strict_types=1);

namespace App\Modules\AbusiveReport\Http\Resources;

use App\Models\AbusiveReport;
use App\Modules\AbusiveReport\Enums\AbusiveReportType;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin AbusiveReport
 */
class AbusiveReportResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $user = $request->user();

        return [
            'id' => $this->id,
            'target' => [
                'id' => $this->model_id,
                'type' => AbusiveReportType::fromModelClass($this->model_type),
            ],
            'message' => $this->message,
            'user_id' => $this->when(
                $user && $user->can('viewAny', AbusiveReport::class),
                $this->user_id
            ),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
