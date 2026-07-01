<?php

declare(strict_types=1);

namespace App\Modules\Shop\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class NearbyShopRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // pencarian toko terdekat bersifat publik
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'lat' => ['required', 'numeric', 'between:-90,90'],
            'lng' => ['required', 'numeric', 'between:-180,180'],
        ];
    }
}
