<?php

declare(strict_types=1);

namespace App\Modules\Type\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermissionTo('super_admin') ?? false;
    }

    public function rules(): array
    {
        $typeId = $this->route('type') ? $this->route('type')->id : null;

        return [
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:types,slug,' . $typeId,
            'language' => 'required|string|size:2',
            'promotional_sliders' => 'nullable|array',
            'images' => 'nullable|array',
            'settings' => 'nullable|array',
            'icon' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:1000',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama type wajib diisi',
            'slug.required' => 'Slug type wajib diisi',
            'slug.unique' => 'Slug type sudah digunakan',
            'language.required' => 'Bahasa wajib diisi',
            'language.size' => 'Bahasa harus 2 karakter (contoh: id, en)',
        ];
    }
}