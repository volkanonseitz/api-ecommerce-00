<?php

declare(strict_types=1);

namespace App\Modules\Payment\Http\Requests;

use App\Models\PaymentMethod;
use Illuminate\Foundation\Http\FormRequest;

class PaymentMethodDeleteRequest extends FormRequest
{
    public function authorize(): bool
    {
        $method = $this->findPaymentMethod();
        
        if (!$method) {
            return false;
        }

        return $this->user()->can('delete', $method);
    }

    public function rules(): array
    {
        return [];
    }

    private function findPaymentMethod(): ?PaymentMethod
    {
        $id = $this->route('id');
        
        if (!is_numeric($id)) {
            return null;
        }

        return PaymentMethod::query()
            ->where('id', (int) $id)
            ->whereHas('paymentGateway', function ($query) {
                $query->where('user_id', $this->user()->id);
            })
            ->first();
    }

    public function getPaymentMethod(): PaymentMethod
    {
        $method = $this->findPaymentMethod();
        
        if (!$method) {
            abort(404, 'Payment method not found or you do not have permission to access it.');
        }

        return $method;
    }
}