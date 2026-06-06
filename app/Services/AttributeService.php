<?php

namespace App\Services;

use App\Models\Attribute;
use App\Models\AttributeValue;
use App\DTO\AttributeData;
use App\DTO\AttributeValueData;
use App\Actions\CreateAttributeAction;
use App\Actions\UpdateAttributeAction;
use App\Enums\Permission;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Arr;

class AttributeService
{
    public function __construct(
        private CreateAttributeAction $createAttribute,
        private UpdateAttributeAction $updateAttribute,
    ) {}

    public function hasPermission(?Authenticatable $user, ?int $shopId): bool
    {
        if (!$user) return false;
        if ($user->hasPermissionTo(Permission::SUPER_ADMIN->value)) return true;
        if (!$shopId) return false;

        $shop = Shop::find($shopId);
        if (!$shop || !$shop->is_active) {
            throw new \Exception(config('notice.SHOP_NOT_APPROVED'));
        }
        if ($user->hasPermissionTo(Permission::STORE_OWNER->value)) {
            return $shop->owner_id === $user->id;
        }
        // Staff tidak diizinkan untuk attribute? Bisa disesuaikan
        return false;
    }

    public function getAttributesByLanguage(string $language)
    {
        return Attribute::where('language', $language)->with(['values', 'shop'])->get();
    }

    public function getAttributeByIdOrSlug($identifier, string $language): Attribute
    {
        if (is_numeric($identifier)) {
            return Attribute::with('values')->where('id', $identifier)->firstOrFail();
        }
        return Attribute::with('values')->where('slug', $identifier)->where('language', $language)->firstOrFail();
    }

    public function createAttribute(AttributeData $data): Attribute
    {
        return $this->createAttribute->execute($data);
    }

    public function updateAttribute(Attribute $attribute, AttributeData $data): Attribute
    {
        return $this->updateAttribute->execute($attribute, $data);
    }

    public function deleteAttribute(Attribute $attribute): void
    {
        $attribute->delete();
    }

    public function exportAttributes(int $shopId): array
    {
        $attributes = Attribute::where('shop_id', $shopId)->with('values')->get();
        $list = $attributes->toArray();
        if (empty($list)) {
            return [];
        }
        // Ubah values menjadi string comma separated
        foreach ($list as &$attr) {
            if (isset($attr['values']) && is_array($attr['values'])) {
                $attr['values'] = implode(',', Arr::pluck($attr['values'], 'value'));
            }
            unset($attr['id'], $attr['created_at'], $attr['updated_at'], $attr['slug'], $attr['translated_languages']);
        }
        return $list;
    }

    public function importAttributes(array $attributesData, int $shopId, $user): void
    {
        foreach ($attributesData as $attributeData) {
            if (!isset($attributeData['name'])) {
                throw new \Exception("WRONG_CSV");
            }
            unset($attributeData['id']);
            $attributeData['shop_id'] = $shopId;
            $values = [];
            if (isset($attributeData['values'])) {
                $values = explode(',', $attributeData['values']);
                unset($attributeData['values']);
            }
            $attribute = Attribute::firstOrCreate($attributeData);
            foreach ($values as $value) {
                $attribute->values()->firstOrCreate(['value' => $value]);
            }
        }
    }
}