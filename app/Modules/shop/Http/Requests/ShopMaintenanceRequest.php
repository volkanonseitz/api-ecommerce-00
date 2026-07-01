<?php

declare(strict_types=1);

namespace App\Modules\Shop\Http\Requests;

use App\Models\Shop;
use Illuminate\Foundation\Http\FormRequest;

class ShopMaintenanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var Shop $shop */
        $shop = $this->route('shop');

        return $this->user()?->can('toggleMaintenance', $shop) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'enable' => ['required', 'boolean'],
        ];
    }
}
