<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Modules\Wallet\Services\WalletService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

final class GiveSignupPointsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public readonly int $userId,
        public readonly int $points,
    ) {}

    public function handle(WalletService $walletService): void
    {
        $walletService->addPoints($this->userId, $this->points);
    }
}
