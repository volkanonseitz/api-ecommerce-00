<?php

declare(strict_types=1);

namespace App\Modules\AbusiveReport\Http\Requests;

use App\Modules\AbusiveReport\Enums\AbusiveReportType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateAbusiveReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Setiap user yang login boleh membuat laporan
        return $this->user() !== null;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'model_id' => ['required', 'integer', 'min:1'],
            'model_type' => ['required', Rule::in(AbusiveReportType::getValues())],
            'message' => ['required', 'string', 'min:10', 'max:1000'],
        ];
    }
}
