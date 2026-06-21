<?php

namespace App\Http\Requests;

use App\Enums\RefundPolicyStatus;
use App\Enums\RefundPolicyTarget;
use Illuminate\Foundation\Http\FormRequest;

class RefundPolicyUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['nullable', 'string', 'max:255'],
            'target' => ['nullable', 'string', 'in:'.implode(',', RefundPolicyTarget::getValues())],
            'status' => ['nullable', 'string', 'in:'.implode(',', RefundPolicyStatus::getValues())],
            'slug' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:10000'],
            'shop_id' => ['nullable', 'exists:shops,id'],
            'language' => ['nullable', 'string'],
        ];
    }
}
