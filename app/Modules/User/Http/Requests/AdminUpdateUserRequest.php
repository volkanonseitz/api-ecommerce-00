<?php

declare(strict_types=1);

namespace App\Modules\User\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AdminUpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        $target = User::find($this->route('id'));

        // Policy-based check (lihat UserPolicy::update) -> mencegah IDOR
        // walau route-level middleware permission sudah ada.
        return $target !== null && ($this->user()?->can('update', $target) ?? false);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $userId = (int) $this->route('id');

        return [
            'name' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email:rfc,dns', Rule::unique('users', 'email')->ignore($userId)],
            'shop_id' => ['nullable', 'integer', 'exists:shops,id'],
            'profile' => ['nullable', 'array'],
            'address' => ['nullable', 'array'],
        ];
    }
}
