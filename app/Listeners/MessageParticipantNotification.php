<?php

namespace App\Listeners;

use App\Models\User;
use App\Modules\Message\Events\MessageSent; // Event yang sudah direstrukturisasi
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification; // Menggunakan facade Notification

class MessageParticipantNotification implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(MessageSent $event): void
    {
        $message = $event->message;
        $conversation = $event->conversation;
        $sender = $event->sender;

        // Mengirim notifikasi ke partisipan lain dalam percakapan
        foreach ($conversation->participants as $participant) {
            // Jangan kirim notifikasi ke pengirim itu sendiri
            if ($participant->user_id !== $sender->id) {
                $recipient = User::find($participant->user_id);
                if ($recipient) {
                    // Asumsi ada Notification::send(...) yang relevan,
                    // atau bisa menggunakan Notification::send()
                    // Untuk saat ini, saya hanya log
                    Log::info('Notifikasi pesan dikirim ke partisipan: '.$recipient->email, ['message_id' => $message->id]);
                    // Contoh: $recipient->notify(new NewMessageNotification($message, $conversation));
                }
            }
        }
    }
}
