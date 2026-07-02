<?php

declare(strict_types=1);

namespace App\Modules\Coupon\Http\Resources;

use App\Models\Coupon; // Use for type hinting if necessary
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin array{
 *     is_valid: bool,
 *     message?: string,
 *     coupon?: Coupon,
 * }
 */
final class CouponVerifyResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'is_valid' => $this['is_valid'],
            'message' => $this['message'] ?? null,
            'coupon' => $this->when($this['coupon'] !== null, function () {
                return new CouponResource($this['coupon']); // Use existing CouponResource
            }),
        ];
    }
}
