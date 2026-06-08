<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Enums\StoreNoticePriority;
use App\Enums\StoreNoticeType;

class StoreNoticeRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        $priorityValues = array_column(StoreNoticePriority::cases(), 'value');
        $typeValues = array_column(StoreNoticeType::cases(), 'value');

        return [
            'priority' => ['required', 'string', Rule::in($priorityValues)],
            'notice' => ['required', 'string'],
            'description' => ['nullable', 'string', 'max:10000'],
            'effective_from' => ['nullable', 'date'],
            'expired_at' => ['required', 'date', 'after:effective_from'],
            'type' => ['required', 'string', Rule::in($typeValues)],
            'received_by' => ['nullable', 'array', 'required_if:type,' . StoreNoticeType::SPECIFIC_VENDOR->value . ',' . StoreNoticeType::SPECIFIC_SHOP->value],
            'received_by.*' => ['nullable', 'integer'],
        ];
    }

    public function messages(): array
    {
        return [
            'received_by.required_if' => 'Please select at least one specific receiver.',
        ];
    }
}