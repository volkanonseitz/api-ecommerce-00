<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BecameSellersRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'page_options' => ['required', 'array'],
            'commissions' => ['nullable', 'array'],
            'commissions.*.min_balance' => ['required', 'numeric', 'min:0'],
            'commissions.*.max_balance' => ['required', 'numeric'],
            'commissions.*.commission' => ['required', 'numeric', 'min:0'],
            'commissions.*.level' => ['required', 'string'],
            'commissions.*.sub_level' => ['required', 'string'],
            'language' => ['nullable', 'string'],
        ];
    }
}