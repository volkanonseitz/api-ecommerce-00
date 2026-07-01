<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Mail\ForgetPassword;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

final class SendForgetPasswordEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public readonly string $email,
        public readonly string $token,
    ) {}

    public function handle(): void
    {
        // Proses berat (kirim email pihak ketiga) tidak lagi memblokir response HTTP.
        Mail::to($this->email)->send(new ForgetPassword($this->token));
    }
}
