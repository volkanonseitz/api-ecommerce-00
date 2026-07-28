<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Notifications\PasswordResetSuccessNotification;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Contracts\Queue\ShouldQueue;

final class SendPasswordResetSuccessNotification implements ShouldQueue
{
    /**
     * Create the event listener.
     */
    public function __construct() {}

    /**
     * Handle the event.
     */
    public function handle(PasswordReset $event): void
    {
        $event->user->notify(new PasswordResetSuccessNotification($event->user));
    }
}
