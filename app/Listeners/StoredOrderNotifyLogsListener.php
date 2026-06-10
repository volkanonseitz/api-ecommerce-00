<?php

namespace App\Listeners;

use App\Events\OrderCreated;
use App\Models\NotifyLogs;
use App\Models\Shop;
use App\Services\UserService;

class StoredOrderNotifyLogsListener
{
    protected UserService $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function handle(OrderCreated $event)
    {
        // Notifikasi ke admin
        $admins = $this->userService->getAdminUsers();
        foreach ($admins as $admin) {
            NotifyLogs::create([
                'receiver' => $admin->id,
                'sender'   => $event->user?->id ?? $event->order->customer_id,
                'notify_type' => 'order',
                'notify_receiver_type' => 'admin',
                'is_read' => false,
                'notify_text' => 'One new order created. Order ID : ' . $event->order->tracking_number,
                'notify_tracker' => $event->order->tracking_number,
            ]);
        }

        // Notifikasi ke vendor
        foreach ($event->order->children as $childOrder) {
            $shop = Shop::find($childOrder->shop_id);
            if ($shop && $shop->owner_id) {
                NotifyLogs::create([
                    'receiver' => $shop->owner_id,
                    'sender'   => $childOrder->customer_id,
                    'notify_type' => 'order',
                    'notify_receiver_type' => 'vendor',
                    'is_read' => false,
                    'notify_text' => 'One new order created. Order ID : ' . $childOrder->tracking_number,
                    'notify_tracker' => $childOrder->tracking_number,
                ]);
            }
        }
    }
}