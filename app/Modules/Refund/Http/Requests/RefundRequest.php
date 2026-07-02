<?php

declare(strict_types=1);

namespace App\Modules\Refund\Http\Requests;

use App\Models\Refund;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;

class RefundRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var User $user */
        $user = $this->user();

        return $user && $user->can('create', Refund::class);
    }

    public function rules(): array
    {
        return [
            'order_id' => ['required', 'exists:orders,id'],
            'title' => ['nullable', 'string'],
            'description' => ['nullable', 'string', 'max:10000'],
            'images' => ['nullable', 'array'],
            'refund_reason_id' => ['nullable', 'exists:refund_reasons,id'],
        ];
    }
}
