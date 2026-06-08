<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Enums\CouponType;

class CouponUpdateRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        $language = $this->language ?? config('shop.default_language', 'en');
        $amountRules = ($this->type === 'percentage')
            ? ['required', 'numeric', 'min:0', 'max:100']
            : ['required', 'numeric', 'min:0'];

        $rules = [
            'description' => ['nullable', 'string', 'max:10000'],
            'image' => ['nullable', 'array'],
            'language' => ['nullable', 'string'],
            'amount' => $amountRules,
            'minimum_cart_amount' => ['nullable', 'numeric', 'min:0'],
        ];

        // Jika language adalah default, boleh update code, type, active_from, expire_at
        if ($language === config('shop.default_language', 'en')) {
            $rules['code'] = ['nullable', 'string', Rule::unique('coupons')->ignore($this->id)->where('language', $language)];
            $rules['type'] = ['nullable', Rule::in(CouponType::values())];
            $rules['active_from'] = ['nullable', 'date'];
            $rules['expire_at'] = ['nullable', 'date'];
        }

        return $rules;
    }
}