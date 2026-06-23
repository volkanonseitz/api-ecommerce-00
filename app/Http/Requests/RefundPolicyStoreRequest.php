<?php

namespace App\Http\Requests;

use App\Enums\RefundPolicyStatus;
use App\Enums\RefundPolicyTarget;
use Illuminate\Foundation\Http\FormRequest;

class RefundPolicyStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'target' => ['required', 'string', 'in:'.implode(',', RefundPolicyTarget::getValues())],
            'status' => ['required', 'string', 'in:'.implode(',', RefundPolicyStatus::getValues())],
            'slug' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:10000'],
            'shop_id' => ['nullable', 'exists:shops,id'],
            'language' => ['nullable', 'string'],
        ];
    }
}
