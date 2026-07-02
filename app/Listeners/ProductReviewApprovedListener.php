<?php

namespace App\Listeners;

use App\Events\ProductReviewApproved;
use App\Models\NotifyLogs;
use App\Modules\User\Services\UserQueryService;
use Illuminate\Contracts\Queue\ShouldQueue;

class ProductReviewApprovedListener implements ShouldQueue
{
    public function __construct(
        private UserQueryService $userService
    ) {}

    public function handle(ProductReviewApproved $event): void
    {
        $product = $event->product;

        $admins = $this->userService->getAdminUsers();

        foreach ($admins as $admin) {
            NotifyLogs::create([
                'receiver' => $admin->id,
                'sender' => null,
                'notify_type' => 'review_approved',
                'notify_receiver_type' => 'admin',
                'is_read' => false,
                'notify_text' => "Product review approved for product: {$product->name}",
                'notify_tracker' => $product->id,
            ]);
        }
    }
}
