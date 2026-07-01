<?php

declare(strict_types=1);

namespace App\Modules\Refund\Http\Requests;

use App\Enums\RefundPolicyStatus;
use App\Enums\RefundPolicyTarget;
use App\Models\RefundPolicy;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;

class RefundPolicyUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var User $user */
        $user = $this->user();
        /** @var RefundPolicy $policy */
        $policy = $this->route('refund_policy');

        return $user && $user->can('update', $policy);
    }

    public function rules(): array
    {
        return [
            'title' => ['nullable', 'string', 'max:255'],
            'target' => ['nullable', 'string', 'in:'.implode(',', RefundPolicyTarget::getValues())],
            'status' => ['nullable', 'string', 'in:'.implode(',', RefundPolicyStatus::getValues())],
            'slug' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:10000'],
            'shop_id' => ['nullable', 'exists:shops,id'],
            'language' => ['nullable', 'string'],
        ];
    }
}
