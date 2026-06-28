<?php

declare(strict_types=1);

namespace App\Modules\Attachment\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AttachmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'attachment' => ['required', 'array'],
            'attachment.*' => ['file', 'max:20480'], // max 20MB per file, opsional
        ];
    }

    public function messages(): array
    {
        return [
            'attachment.required' => 'At least one file is required.',
            'attachment.*.file' => 'Each attachment must be a valid file.',
            'attachment.*.max' => 'Each attachment must not exceed 20MB.',
        ];
    }
}
