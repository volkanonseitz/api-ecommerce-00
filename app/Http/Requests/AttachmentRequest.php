<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AttachmentRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'attachment' => ['required', 'array'],
            'attachment.*' => ['file'], // setiap item harus file
        ];
    }

    public function messages(): array
    {
        return [
            'attachment.required' => 'At least one file is required.',
            'attachment.*.file' => 'Each attachment must be a valid file.',
        ];
    }
}