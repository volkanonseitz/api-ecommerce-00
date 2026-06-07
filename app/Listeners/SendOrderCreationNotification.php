<?php

namespace App\Listeners;

use App\Events\OrderCreated;
use Illuminate\Support\Facades\Mail;

class SendOrderCreationNotification
{
    public function handle(OrderCreated $event)
    {
        // Kirim email ke customer
        Mail::to($event->order->customer->email)->send(new \App\Mail\OrderCreated($event->order));
    }
}