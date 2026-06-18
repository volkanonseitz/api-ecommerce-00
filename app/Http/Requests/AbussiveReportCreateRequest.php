<?php

namespace App\Http\Requests;

use App\Enums\AbusiveReportTypes;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class AbusiveReportCreateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'model_id' => [
                'required',
                'integer',
                'min:1',
            ],

            'model_type' => [
    'required',
    Rule::in(AbusiveReportType::getValues()),
],

            'message' => [
                'required',
                'string',
                'min:10',
                'max:1000',
            ],
        ];
    }
}