<?php

declare(strict_types=1);

namespace App\Modules\Shop\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FollowShopRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Wajib login (sudah dijamin middleware auth:sanctum di route group),
        // tidak ada syarat kepemilikan karena user hanya follow/unfollow diri sendiri.
        return $this->user() !== null;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'shop_id' => ['required', 'integer', 'exists:shops,id'],
        ];
    }
}
