<?php

declare(strict_types=1);

namespace App\Modules\Analytics\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AnalyticsRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Otorisasi dilakukan di controller via Gate
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'language' => ['sometimes', 'string', 'in:id,en'], // sesuaikan dengan bahasa yang tersedia
            'limit' => ['sometimes', 'integer', 'min:1', 'max:100'],
            'type_id' => ['sometimes', 'integer', 'exists:types,id'],
            'type_slug' => ['sometimes', 'string', 'exists:types,slug'],
            'shop_id' => ['sometimes', 'integer', 'exists:shops,id'],
            'days' => ['sometimes', 'integer', 'min:1', 'max:365'],
        ];
    }
}
