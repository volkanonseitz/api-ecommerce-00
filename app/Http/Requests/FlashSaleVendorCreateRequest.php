<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FlashSaleVendorCreateRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string'],
            'note' => ['nullable', 'string'],
            'flash_sale_id' => ['required', 'exists:flash_sales,id'],
            'language' => ['nullable', 'string'],
            'requested_product_ids' => ['nullable', 'array'],
            'requested_product_ids.*' => ['exists:products,id'],
        ];
    }
}