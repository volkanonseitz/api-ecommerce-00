<?php

namespace App\Services;

use App\Models\Faqs;
use App\Models\Shop;
use App\DTO\FaqsData;
use App\Actions\CreateFaqsAction;
use App\Actions\UpdateFaqsAction;
use App\Enums\Permission;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\Request;

class FaqsService
{
    public function __construct(
        private CreateFaqsAction $createFaqs,
        private UpdateFaqsAction $updateFaqs,
    ) {}

    public function hasPermission(?Authenticatable $user, ?int $shopId): bool
    {
        if (!$user) return false;
        if ($user->hasPermissionTo(Permission::SUPER_ADMIN->value)) return true;
        if (!$shopId) return false;
        $shop = Shop::find($shopId);
        if (!$shop) return false;
        if ($user->hasPermissionTo(Permission::STORE_OWNER->value)) {
            return $shop->owner_id === $user->id;
        }
        if ($user->hasPermissionTo(Permission::STAFF->value)) {
            return $shop->staffs->contains($user->id);
        }
        return false;
    }

    public function getFaqsQuery(Request $request, ?Authenticatable $user)
    {
        $language = $request->language ?? config('shop.default_language', 'en');
        $query = Faqs::with('shop')->where('language', $language);

        if (!$user) {
            if ($request->shop_id) {
                $query->where('shop_id', $request->shop_id);
            }
            return $query;
        }

        if ($user->hasPermissionTo(Permission::SUPER_ADMIN->value)) {
            return $query;
        }

        if ($user->hasPermissionTo(Permission::STORE_OWNER->value)) {
            if ($request->shop_id && $this->hasPermission($user, $request->shop_id)) {
                return $query->where('shop_id', $request->shop_id);
            }
            return $query->where('user_id', $user->id)->whereIn('shop_id', $user->shops->pluck('id'));
        }

        if ($user->hasPermissionTo(Permission::STAFF->value)) {
            if ($request->shop_id) {
                return $query->where('shop_id', $request->shop_id);
            }
            return $query->where('shop_id', $user->shop_id);
        }

        // customer
        return $query;
    }

    public function findOrFail(int $id): Faqs
    {
        return Faqs::with('shop')->findOrFail($id);
    }

    public function store(FaqsData $data): Faqs
    {
        return $this->createFaqs->execute($data);
    }

    public function update(Faqs $faqs, FaqsData $data): Faqs
    {
        return $this->updateFaqs->execute($faqs, $data);
    }

    public function delete(Faqs $faqs): void
    {
        $faqs->delete();
    }
}