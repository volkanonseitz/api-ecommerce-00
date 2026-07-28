<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Webhook;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

final class WebhookDispatcher
{
    public function dispatch(Webhook $webhook, string $event, array $payload): void
    {
        if (! $webhook->is_active) {
            return;
        }

        // Check if the webhook is subscribed to this event
        if (! in_array($event, $webhook->events, true)) {
            return;
        }

        try {
            $data = json_encode($payload, JSON_THROW_ON_ERROR);
            $signature = hash_hmac('sha256', $data, $webhook->secret);

            Http::withHeaders([
                'X-Webhook-Signature' => $signature,
                'X-Webhook-Event' => $event,
            ])->timeout(5)->post($webhook->url, $payload);

            $webhook->update(['last_triggered_at' => now()]);
        } catch (\JsonException $e) {
            Log::error('Webhook JSON encoding error: '.$e->getMessage(), ['webhook_id' => $webhook->id, 'event' => $event]);
        } catch (\Throwable $e) {
            Log::error('Webhook dispatch failed: '.$e->getMessage(), ['webhook_id' => $webhook->id, 'event' => $event, 'url' => $webhook->url]);
        }
    }
}
