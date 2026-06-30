<?php

declare(strict_types=1);

namespace App\Modules\Feedback\Http\Resources;

use App\Models\Feedback;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Feedback
 */
class FeedbackResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'model_id' => $this->model_id,
            'model_type' => $this->model_type,
            'positive' => $this->positive,
            'negative' => $this->negative,
            'user_id' => $this->user_id,
            'user' => $this->whenLoaded('user', function () {
                return [
                    'id' => $this->user->id,
                    'name' => $this->user->name,
                    'email' => $this->user->email,
                ];
            }),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
