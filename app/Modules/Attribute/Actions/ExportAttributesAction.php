<?php

declare(strict_types=1);

namespace App\Modules\Attribute\Actions;

use App\Models\Attribute;
use App\Models\User;
use Illuminate\Support\Arr;

final class ExportAttributesAction
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public function execute(int $shopId, User $user): array
    {
        $attributes = Attribute::where('shop_id', $shopId)->with('values')->get();
        $list = $attributes->toArray();
        if (empty($list)) {
            return [];
        }
        foreach ($list as &$attr) {
            if (isset($attr['values']) && is_array($attr['values'])) {
                $attr['values'] = implode(',', Arr::pluck($attr['values'], 'value'));
            }
            unset($attr['id'], $attr['created_at'], $attr['updated_at'], $attr['slug'], $attr['translated_languages']);
        }

        return $list;
    }
}
