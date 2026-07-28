<?php

declare(strict_types=1);

namespace App\Modules\StoreNotice\Services;

use App\Enums\Permission;
use App\Enums\StoreNoticeType;
use App\Models\Shop;
use App\Models\StoreNotice;
use App\Models\User;
use App\Modules\NotifyLogs\Events\StoreNoticeEvent;
use App\Modules\StoreNotice\Actions\CreateStoreNoticeAction;
use App\Modules\StoreNotice\Actions\DeleteStoreNoticeAction;
use App\Modules\StoreNotice\Actions\UpdateStoreNoticeAction;
use App\Modules\StoreNotice\DTO\StoreNoticeData;
use Illuminate\Contracts\Auth\Authenticatable;

final class StoreNoticeWriteService
{
    public function __construct(
        private readonly CreateStoreNoticeAction $createStoreNoticeAction,
        private readonly UpdateStoreNoticeAction $updateStoreNoticeAction,
        private readonly DeleteStoreNoticeAction $deleteStoreNoticeAction,
    ) {}

    public function createStoreNotice(StoreNoticeData $data, Authenticatable $creator): StoreNotice
    {
        $storeNotice = $this->createStoreNoticeAction->execute($data);
        $this->syncUsersOrShops($storeNotice, $data->type, $data->received_by);
        $this->syncReadStatus($storeNotice, $data->received_by);
        event(new StoreNoticeEvent($storeNotice, 'create', $creator));

        return $storeNotice;
    }

    public function updateStoreNotice(StoreNotice $storeNotice, StoreNoticeData $data, Authenticatable $updater): StoreNotice
    {
        $updated = $this->updateStoreNoticeAction->execute($storeNotice, $data);
        $this->syncUsersOrShops($updated, $data->type, $data->received_by);
        $this->syncReadStatus($updated, $data->received_by);
        event(new StoreNoticeEvent($updated, 'update', $updater));

        return $updated;
    }

    public function deleteStoreNotice(StoreNotice $storeNotice): void
    {
        $this->deleteStoreNoticeAction->execute($storeNotice);
    }

    private function syncUsersOrShops(StoreNotice $notice, string $type, ?array $receivedBy): void
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

    private function syncReadStatus(StoreNotice $notice, ?array $receivedBy): void
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
}
