<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Enums\RefundPolicyTarget;
use App\Enums\RefundPolicyStatus;

class RefundPolicyUpdateRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'title' => ['nullable', 'string', 'max:255'],
            'target' => ['nullable', 'string', 'in:' . implode(',', RefundPolicyTarget::values())],
            'status' => ['nullable', 'string', 'in:' . implode(',', RefundPolicyStatus::values())],
            'slug' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:10000'],
            'shop_id' => ['nullable', 'exists:shops,id'],
            'language' => ['nullable', 'string'],
        ];
    }
}