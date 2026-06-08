<?php

namespace App\Services;

use App\Models\TermsAndConditions;
use App\Models\Shop;
use App\DTO\TermsData;
use App\Enums\Permission;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\Request;

class TermsService
{
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

    public function getTermsQuery(Request $request, ?Authenticatable $user)
    {
        $language = $request->language ?? config('shop.default_language', 'en');
        $query = TermsAndConditions::with('shop')->where('language', $language);

        if ($user && $user->hasPermissionTo(Permission::SUPER_ADMIN->value)) {
            return $query;
        }

        if ($user && $user->hasPermissionTo(Permission::STORE_OWNER->value)) {
            if ($request->shop_id && $this->hasPermission($user, $request->shop_id)) {
                return $query->where('shop_id', $request->shop_id);
            }
            return $query->whereIn('shop_id', $user->shops->pluck('id'));
        }

        if ($user && $user->hasPermissionTo(Permission::STAFF->value)) {
            if ($request->shop_id && $this->hasPermission($user, $request->shop_id)) {
                return $query->where('shop_id', $request->shop_id);
            }
            return $query->where('shop_id', $user->shop_id);
        }

        // Guest or customer
        if ($request->shop_id) {
            return $query->where('shop_id', $request->shop_id)->where('is_approved', true);
        }
        return $query->where('is_approved', true);
    }

    public function store(TermsData $data): TermsAndConditions
    {
        $isApproved = ($data->shop_id === null || $data->shop_id === 0);
        $shop = $data->shop_id ? Shop::find($data->shop_id) : null;
        $issuedBy = $shop ? $shop->name : 'Super Admin';
        $type = $data->shop_id ? 'shop' : 'global';

        return TermsAndConditions::create([
            'title' => $data->title,
            'description' => $data->description,
            'language' => $data->language,
            'slug' => $data->slug,
            'user_id' => $data->user_id,
            'shop_id' => $data->shop_id,
            'type' => $type,
            'issued_by' => $issuedBy,
            'is_approved' => $isApproved,
        ]);
    }

    public function update(TermsAndConditions $term, array $data): TermsAndConditions
    {
        $term->update($data);
        return $term->fresh();
    }

    public function approve(TermsAndConditions $term): void
    {
        $term->is_approved = true;
        $term->save();
    }

    public function disapprove(TermsAndConditions $term): void
    {
        $term->is_approved = false;
        $term->save();
    }

    public function delete(TermsAndConditions $term): void
    {
        $term->delete();
    }
}