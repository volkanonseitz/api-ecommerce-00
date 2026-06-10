<?php

namespace App\Listeners;

use App\Events\OrderCreated;
use App\Models\NotifyLogs;
use App\Models\Shop;
use App\Models\User;
use App\Traits\UsersTrait;

class StoredOrderNotifyLogsListener
{
    use UsersTrait;

    public function handle(OrderCreated $event)
    {
        // Notifikasi ke admin
        $admins = $this->getAdminUsers();
        foreach ($admins as $admin) {
            NotifyLogs::create([
                'receiver' => $admin->id,
                'sender' => $event->user?->id ?? $event->order->customer_id,
                'notify_type' => 'order',
                'notify_receiver_type' => 'admin',
                'is_read' => false,
                'notify_text' => 'One new order created. Order ID : ' . $event->order->tracking_number,
                'notify_tracker' => $event->order->tracking_number,
            ]);
        }

        // Notifikasi ke vendor untuk setiap child order
        $childOrders = $event->order->children;
        foreach ($childOrders as $child) {
            $shop = Shop::find($child->shop_id);
            if ($shop && $shop->owner_id) {
                NotifyLogs::create([
                    'receiver' => $shop->owner_id,
                    'sender' => $child->customer_id,
                    'notify_type' => 'order',
                    'notify_receiver_type' => 'vendor',
                    'is_read' => false,
                    'notify_text' => 'One new order created. Order ID : ' . $child->tracking_number,
                    'notify_tracker' => $child->tracking_number,
                ]);
            }
        }
    }
}