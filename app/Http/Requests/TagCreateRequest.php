<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class TagCreateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string',
            'slug' => 'nullable|string',
            'type_id' => 'nullable|exists:types,id',
            'icon' => 'nullable|string',
            'image' => 'nullable|array',
            'details' => 'nullable|string',
            'language' => 'nullable|string',
        ];
    }
}
