<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UserUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $userId = $this->route('id'); // atau dari user yang login jika update sendiri
        if (!$userId && $this->user()) {
            $userId = $this->user()->id;
        }

        return [
            'name' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', Rule::unique('users')->ignore($userId)],
            'shop_id' => ['nullable', 'exists:shops,id'],
            'profile' => ['nullable', 'array'],
            'address' => ['nullable', 'array'],
        ];
    }
}
