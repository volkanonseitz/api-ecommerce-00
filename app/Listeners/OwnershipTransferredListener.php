<?php

namespace App\Listeners;

use App\Events\ProcessOwnershipTransition;
use App\Modules\User\Services\UserQueryService;
use App\Notifications\OwnershipTransferred;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class OwnershipTransferredListener implements ShouldQueue
{
    public function __construct(private UserQueryService $UserQueryService) {}

    public function handle(ProcessOwnershipTransition $event)
    {
        try {
            $shop = $event->shop;
            $previousOwner = $event->previousOwner;
            $newOwner = $event->newOwner;

            $admins = $this->UserQueryService->getAdminUsers();
            $users = $admins->merge([$previousOwner, $newOwner]);

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
