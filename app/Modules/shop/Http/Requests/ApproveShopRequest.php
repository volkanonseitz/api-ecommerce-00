<?php

declare(strict_types=1);

namespace App\Modules\Shop\Http\Requests;

use App\Models\Shop;
use Illuminate\Foundation\Http\FormRequest;

class ApproveShopRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var Shop $shop */
        $shop = $this->route('shop');

        return $this->user()?->can('approve', $shop) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'admin_commission_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'is_custom_commission' => ['nullable', 'boolean'],
        ];
    }
}
