<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Enums\ResourceType;

class ResourceUpdateRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string'],
            'type' => ['required', Rule::in(ResourceType::values())],
            'price' => ['nullable', 'numeric'],
            'is_approved' => ['nullable', 'boolean'],
            'image' => ['nullable', 'array'],
            'icon' => ['nullable', 'string'],
            'details' => ['nullable', 'string'],
            'language' => ['nullable', 'string'],
        ];
    }
}