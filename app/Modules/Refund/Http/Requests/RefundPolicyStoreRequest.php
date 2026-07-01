<?php

declare(strict_types=1);

namespace App\Modules\Refund\Http\Requests;

use App\Enums\Permission;
use App\Enums\RefundPolicyStatus;
use App\Enums\RefundPolicyTarget;
use App\Models\RefundPolicy;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;

class RefundPolicyStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var User $user */
        $user = $this->user();

        return $user && $user->can('create', RefundPolicy::class);
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'target' => ['required', 'string', 'in:'.implode(',', RefundPolicyTarget::getValues())],
            'status' => ['required', 'string', 'in:'.implode(',', RefundPolicyStatus::getValues())],
            'slug' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:10000'],
            'shop_id' => ['nullable', 'exists:shops,id'],
            'language' => ['nullable', 'string'],
        ];
    }
}
