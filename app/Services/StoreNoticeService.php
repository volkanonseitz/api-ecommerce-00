<?php

namespace App\Services;

use App\Actions\CreateStoreNoticeAction;
use App\Actions\UpdateStoreNoticeAction;
use App\DTO\StoreNoticeData;
use App\Enums\Permission;
use App\Enums\StoreNoticeType;
use App\Events\StoreNoticeEvent;
use App\Models\Shop;
use App\Models\StoreNotice;
use App\Models\User;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class StoreNoticeService
{
    public function __construct(
        private CreateStoreNoticeAction $createStoreNotice,
        private UpdateStoreNoticeAction $updateStoreNotice,
    ) {}

    public function hasPermission(?Authenticatable $user, ?int $shopId): bool
    {
        if (! $user) {
            return false;
        }
        if ($user->hasPermissionTo(Permission::SUPER_ADMIN->value)) {
            return true;
        }
        if (! $shopId) {
            return false;
        }

        $shop = Shop::find($shopId);
        if (! $shop) {
            return false;
        }

        if ($user->hasPermissionTo(Permission::STORE_OWNER->value)) {
            return $shop->owner_id === $user->id;
        }

        return false;
    }

    public function getStoreNoticesQuery(Request $request, ?Authenticatable $user): Builder
    {
        $query = StoreNotice::whereNotNull('id')->whereDate('expired_at', '>=', Carbon::now());

        if (! $user) {
            // guest, untuk shop
            $shopId = $request->shop_id ?? 0;
            if ($shopId) {
                $shop = Shop::where('id', $shopId)->orWhere('slug', $shopId)->first();
                if ($shop) {
                    $query->where('created_by', $shop->owner_id)
                        ->whereHas('shops', fn ($q) => $q->where('id', $shop->id));
                }
            }

            return $query;
        }

        if ($user->hasPermissionTo(Permission::SUPER_ADMIN->value)) {
            return $query;
        }

        // authenticated non-admin
        if ($request->shop_id) {
            $shop = Shop::find($request->shop_id);
            if ($shop) {
                $query->where('created_by', $shop->owner_id)->whereHas('shops', fn ($q) => $q->where('id', $shop->id));
            }
        } elseif ($user->managed_shop) {
            $shopId = $user->managed_shop->id;
            $query->where('created_by', $user->managed_shop->owner_id)
                ->whereHas('shops', fn ($q) => $q->where('id', $shopId));
        } else {
            $query->where('created_by', $user->id)
                ->orWhereHas('users', fn ($q) => $q->where('id', $user->id));
        }

        return $query;
    }

    public function getStoreNoticeTypes(?Authenticatable $user): array
    {
        if ($user && $user->hasPermissionTo(Permission::SUPER_ADMIN->value)) {
            return [
                ['name' => 'ALL VENDOR', 'value' => StoreNoticeType::ALL_VENDOR->value],
                ['name' => 'SPECIFIC VENDOR', 'value' => StoreNoticeType::SPECIFIC_VENDOR->value],
            ];
        }

        return [
            ['name' => 'ALL SHOP', 'value' => StoreNoticeType::ALL_SHOP->value],
            ['name' => 'SPECIFIC SHOP', 'value' => StoreNoticeType::SPECIFIC_SHOP->value],
        ];
    }

    public function getUsersToNotify(Request $request, ?Authenticatable $user)
    {
        if ($user->hasPermissionTo(Permission::SUPER_ADMIN->value)) {
            return User::permission(Permission::STORE_OWNER->value)->orderBy('name')->get();
        }

        return $user->shops()->where('is_active', true)->get();
    }

    public function createStoreNotice(StoreNoticeData $data, Authenticatable $creator): StoreNotice
    {
        $storeNotice = $this->createStoreNotice->execute($data);
        $this->syncUsersOrShops($storeNotice, $data->type, $data->received_by);
        $this->syncReadStatus($storeNotice, $data->received_by);
        event(new StoreNoticeEvent($storeNotice, 'create', $creator));

        return $storeNotice;
    }

    public function updateStoreNotice(StoreNotice $storeNotice, StoreNoticeData $data, Authenticatable $updater): StoreNotice
    {
        $updated = $this->updateStoreNotice->execute($storeNotice, $data);
        $this->syncUsersOrShops($updated, $data->type, $data->received_by);
        $this->syncReadStatus($updated, $data->received_by);
        event(new StoreNoticeEvent($updated, 'update', $updater));

        return $updated;
    }

    public function deleteStoreNotice(StoreNotice $storeNotice): void
    {
        $storeNotice->forceDelete();
    }

    private function syncUsersOrShops(StoreNotice $notice, string $type, ?array $receivedBy)
    {
        if ($type === StoreNoticeType::ALL_VENDOR->value) {
            $users = User::permission(Permission::STORE_OWNER->value)->get();
            $notice->users()->sync($users->pluck('id'));
        } elseif ($type === StoreNoticeType::SPECIFIC_VENDOR->value && $receivedBy) {
            $notice->users()->sync($receivedBy);
        } elseif ($type === StoreNoticeType::ALL_SHOP->value) {
            $shops = Shop::where('is_active', true)->get();
            $notice->shops()->sync($shops->pluck('id'));
        } elseif ($type === StoreNoticeType::SPECIFIC_SHOP->value && $receivedBy) {
            $notice->shops()->sync($receivedBy);
        }
    }

    private function syncReadStatus(StoreNotice $notice, ?array $receivedBy)
    {
        $userIds = [];
        if ($notice->type === StoreNoticeType::ALL_VENDOR->value) {
            $userIds = User::permission(Permission::STORE_OWNER->value)->pluck('id')->toArray();
        } elseif ($notice->type === StoreNoticeType::SPECIFIC_VENDOR->value && $receivedBy) {
            $userIds = $receivedBy;
        } elseif ($notice->type === StoreNoticeType::ALL_SHOP->value) {
            $shopIds = Shop::where('is_active', true)->pluck('id')->toArray();
            $userIds = Shop::whereIn('id', $shopIds)->with('owner')->get()->pluck('owner.id')->toArray();
        } elseif ($notice->type === StoreNoticeType::SPECIFIC_SHOP->value && $receivedBy) {
            $userIds = Shop::whereIn('id', $receivedBy)->with('owner')->get()->pluck('owner.id')->toArray();
        }
        $userIds = array_unique(array_filter($userIds));
        foreach ($userIds as $userId) {
            $notice->read_status()->syncWithoutDetaching([$userId => ['is_read' => false]]);
        }
    }

    public function markAsRead(StoreNotice $notice, int $userId): void
    {
        $notice->read_status()->syncWithoutDetaching([$userId => ['is_read' => true]]);
    }

    public function markMultipleAsRead(array $noticeIds, int $userId): void
    {
        foreach ($noticeIds as $noticeId) {
            $notice = StoreNotice::find($noticeId);
            if ($notice) {
                $notice->read_status()->syncWithoutDetaching([$userId => ['is_read' => true]]);
            }
        }
    }

    public function unmarkAsRead(StoreNotice $notice, int $userId): void
    {
        $notice->read_status()->detach($userId);
        // atau bisa juga update jadi false, tapi di kode awal dihapus lalu dibuat baru
        // Saya ikuti logika awal: hapus lalu buat baru dengan is_read = true
        // Tapi untuk unread, tidak ada di method asli. Untuk read, method readSingleNotice menghapus lalu membuat baru.
        // Jadi saya akan implementasi ulang: untuk read, hapus relasi lalu attach dengan is_read true.
        $notice->read_status()->detach($userId);
        $notice->read_status()->attach($userId, ['is_read' => true]);
    }
}
