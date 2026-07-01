<?php

declare(strict_types=1);

namespace App\Modules\Settings\Http\Requests;

use App\Enums\Permission;
use App\Models\Settings;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;

class SettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var User $user */
        $user = $this->user();

        // Only super_admin can create or update settings
        return $user && $user->can('create', Settings::class);
    }

    public function rules(): array
    {
        return [
            'options' => ['required', 'array'],
            'language' => ['nullable', 'string'],
        ];
    }
}
