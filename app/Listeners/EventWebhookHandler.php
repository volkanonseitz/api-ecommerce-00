<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Models\Webhook;
use App\Services\WebhookDispatcher;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use ReflectionClass;

final class EventWebhookHandler implements ShouldQueue
{
    use InteractsWithQueue;

    public function __construct(
        private readonly WebhookDispatcher $dispatcher
    ) {}

    public function handle(object $event): void
    {
        $eventName = (new ReflectionClass($event))->getShortName();

        // Find all active webhooks subscribed to this event
        $webhooks = Webhook::whereJsonContains('events', $eventName)
            ->where('is_active', true)
            ->get();

        foreach ($webhooks as $webhook) {
            $this->dispatcher->dispatch($webhook, $eventName, (array) $event);
        }
    }
}
