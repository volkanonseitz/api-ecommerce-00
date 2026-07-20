<?php

namespace App\Listeners;

use App\Events\OwnershipTransferStatusControl;
use App\Models\Product;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class OwnershipTransferStatusControlListener implements ShouldQueue
{
    public function __construct(private UserService $userService) {}

    public function handle(OwnershipTransferStatusControl $event)
    {
        $ownershipRequest = $event->ownershipTransfer;

        switch ($ownershipRequest->status) {
            case 'processing':
                $this->processingOwnershipTransferStatus($ownershipRequest);
                break;
            case 'approved':
                $this->approvedOwnershipTransferStatus($ownershipRequest);
                break;
            case 'rejected':
                $this->rejectingOwnershipTransferStatus($ownershipRequest);
                break;
        }
    }

    protected function processingOwnershipTransferStatus($ownershipRequest): void
    {
        $shop = $ownershipRequest->shop;
        $shop->is_active = false;
        $shop->save();

        Product::where('shop_id', $ownershipRequest->shop_id)->update(['status' => 'draft']);

        $message = [
            'message' => 'Shop transfer request #'.$ownershipRequest->transaction_identifier.' is on processing.',
        ];
        $this->sendNotifications($shop, $ownershipRequest, $message);
    }

    protected function approvedOwnershipTransferStatus($ownershipRequest): void
    {
        $shop = $ownershipRequest->shop;
        $shop->owner_id = $ownershipRequest->to;
        $shop->save();

        $message = [
            'message' => 'Congratulations! Shop transfer request #'.$ownershipRequest->transaction_identifier.' is approved.',
        ];
        $this->sendNotifications($shop, $ownershipRequest, $message);
    }

    protected function rejectingOwnershipTransferStatus($ownershipRequest): void
    {
        $shop = $ownershipRequest->shop;
        $shop->is_active = false;
        $shop->save();

        Product::where('shop_id', $ownershipRequest->shop_id)->update(['status' => 'draft']);

        $message = [
            'message' => 'Sorry! Shop transfer request #'.$ownershipRequest->transaction_identifier.' is rejected. For more details please contact with site admin.',
        ];
        $this->sendNotifications($shop, $ownershipRequest, $message);
    }

    protected function sendNotifications($shop, $ownershipRequest, array $message): void
    {
        $previousOwner = User::find($ownershipRequest->from);
        $newOwner = User::find($ownershipRequest->to);

        if (! $previousOwner || ! $newOwner) {
            Log::warning('Previous or new owner not found for ownership transfer', [
                'from' => $ownershipRequest->from,
                'to' => $ownershipRequest->to,
                'transaction' => $ownershipRequest->transaction_identifier,
            ]);

            return;
        }

        $admins = $this->userService->getAdminUsers();
        $users = $admins->push($previousOwner, $newOwner);

        foreach ($users as $user) {
            Notification::route('mail', $user->email)
                ->notify(new TransferredShopOwnershipStatus(
                    $shop,
                    $previousOwner,
                    $newOwner,
                    $message
                ));
        }
    }
}
