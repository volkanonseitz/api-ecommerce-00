<?php

namespace App\Modules\Withdraw\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class WithdrawUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // Tidak ada validasi karena endpoint update tidak digunakan
        ];
    }
}
