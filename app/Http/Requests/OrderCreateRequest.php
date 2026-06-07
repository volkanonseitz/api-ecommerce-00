<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class OrderCreateRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'coupon_id' => 'nullable|exists:coupons,id',
            'shop_id' => 'nullable|exists:shops,id',
            'customer_id' => 'nullable|exists:users,id',
            'language' => 'nullable|string',
            'amount' => 'required|numeric',
            'paid_total' => 'required|numeric',
            'total' => 'required|numeric',
            'delivery_time' => 'nullable|string',
            'customer_contact' => 'required|string',
            'customer_name' => 'nullable|string',
            'payment_gateway' => ['required', Rule::in(PaymentGatewayType::getValues())],
            'altered_payment_gateway' => 'nullable|string',
            'products' => 'required|array',
            'card' => 'nullable|array',
            'token' => 'nullable|string',
            'use_wallet_points' => 'nullable|boolean',
            'shipping_address' => 'nullable|array',
            'billing_address' => 'nullable|array',
            'note' => 'nullable|string',
        ];
    }
}