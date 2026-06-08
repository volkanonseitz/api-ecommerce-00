<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Enums\CouponType;

class CouponCreateRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        $language = $this->language ?? config('shop.default_language', 'en');
        $amountRules = ($this->type === 'percentage') 
            ? ['required', 'numeric', 'min:0', 'max:100']
            : ['required', 'numeric', 'min:0'];

        return [
            'code' => ['required', Rule::unique('coupons')->where('language', $language)],
            'amount' => $amountRules,
            'minimum_cart_amount' => ['required', 'numeric', 'min:0'],
            'shop_id' => ['nullable', 'exists:shops,id'],
            'type' => ['required', Rule::in(CouponType::values())],
            'description' => ['nullable', 'string'],
            'image' => ['nullable', 'array'],
            'language' => ['nullable', 'string'],
            'active_from' => ['required', 'date'],
            'expire_at' => ['required', 'date'],
        ];
    }
}