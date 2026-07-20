<?php

namespace App\Listeners;

use App\Events\ProcessOwnershipTransition;
use App\Notifications\OwnershipTransferred;
use App\Services\UserService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class OwnershipTransferredListener implements ShouldQueue
{
    public function __construct(private UserService $userService) {}

    public function handle(ProcessOwnershipTransition $event)
    {
        try {
            $shop = $event->shop;
            $previousOwner = $event->previousOwner;
            $newOwner = $event->newOwner;

            $admins = $this->userService->getAdminUsers();
            $users = $admins->push($previousOwner, $newOwner);

            foreach ($users as $user) {
                Notification::route('mail', $user->email)
                    ->notify(new OwnershipTransferred(
                        $shop,
                        $previousOwner,
                        $newOwner,
                        $event->optional
                    ));
            }
        } catch (\Throwable $th) {
            Log::error('Error from OwnershipTransferredListener: '.$th->getMessage());
        }
    }
}
