<?php

namespace App\Listeners;

use App\Events\OrderCreated;
use App\Mail\OrderCreated as OrderCreatedMail;
use Illuminate\Support\Facades\Mail;

class SendOrderCreationNotification
{
    public function handle(OrderCreated $event)
    {
        Mail::to($event->order->customer->email)->send(new OrderCreatedMail($event->order));
    }
}
