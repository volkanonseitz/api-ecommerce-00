<?php

declare(strict_types=1);

namespace App\Modules\Coupon\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class DisapproveCouponRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Policy in controller
    }

    public function rules(): array
    {
        return [
            'id' => ['required', 'exists:coupons,id'],
        ];
    }
}
