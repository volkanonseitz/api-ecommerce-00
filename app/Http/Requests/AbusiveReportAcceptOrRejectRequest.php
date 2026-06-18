<?php

namespace App\Http\Requests;

use App\Enums\AbusiveReportTypes;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AbusiveReportAcceptOrRejectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('manage-abusive-reports') ?? false;
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
        ];
    }
}