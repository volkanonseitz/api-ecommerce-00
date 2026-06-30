<?php

declare(strict_types=1);

namespace App\Modules\Message\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MessageCreateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'message' => ['required', 'string'],
        ];
    }
}
