<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TransferShopOwnerShipRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'shop_id' => ['required', 'exists:shops,id'],
            'vendor_id' => ['required', 'exists:users,id'],
            'message' => ['nullable', 'string'],
            'vendorMessage' => ['nullable', 'string'],
        ];
    }
}