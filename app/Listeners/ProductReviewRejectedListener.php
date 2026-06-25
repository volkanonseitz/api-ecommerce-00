<?php

namespace App\Listeners;

use App\Events\ProductReviewRejected;
use App\Models\NotifyLogs;
use App\Services\UserService;
use Illuminate\Contracts\Queue\ShouldQueue;

class ProductReviewRejectedListener implements ShouldQueue
{
    public function __construct(private UserService $userService) {}

    public function handle(ProductReviewRejected $event)
    {
        $product = $event->product;

        // Notifikasi ke admin
        $admins = $this->userService->getAdminUsers();
        foreach ($admins as $admin) {
            NotifyLogs::create([
                'receiver' => $admin->id,
                'sender' => null,
                'notify_type' => 'review_rejected',
                'notify_receiver_type' => 'admin',
                'is_read' => false,
                'notify_text' => "Product review rejected for product: {$product->name}",
                'notify_tracker' => $product->id,
            ]);
        }
    }
}
