<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AbusiveReportAcceptOrRejectRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'model_id' => ['required', 'integer'],
            'model_type' => ['required', 'string'],
        ];
    }
}