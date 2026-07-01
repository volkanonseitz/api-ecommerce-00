<?php

declare(strict_types=1);

namespace App\Modules\User\Actions;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

/**
 * Dipisah dari AuthService (Fat Service) agar logika lockout/brute-force
 * punya satu tanggung jawab tunggal dan mudah diuji terpisah.
 */
final class AttemptLoginAction
{
    /**
     * @return array{status: string, user?: User, locked_until?: \Illuminate\Support\Carbon}
     */
    public function execute(string $email, string $password, Request $request): array
    {
        $user = User::where('email', $email)->first();

        if (! $user) {
            // Timing-attack mitigation: tetap hash walau user tidak ditemukan.
            Hash::check('dummy_password', '$2y$10$'.str_repeat('0', 53));

            return ['status' => 'invalid'];
        }

        if ($user->locked_until?->isFuture()) {
            return ['status' => 'locked', 'locked_until' => $user->locked_until];
        }

        if (! $user->is_active || ! Hash::check($password, $user->password)) {
            $this->registerFailedAttempt($user, $request, $email);

            return ['status' => 'invalid'];
        }

        if (! $user->hasVerifiedEmail()) {
            return ['status' => 'unverified', 'user' => $user];
        }

        $this->registerSuccessfulLogin($user, $request);

        return ['status' => 'success', 'user' => $user->fresh()];
    }

    private function registerFailedAttempt(User $user, Request $request, string $email): void
    {
        DB::transaction(function () use ($user, $request, $email): void {
            $user->increment('failed_login_attempts');

            if ($user->failed_login_attempts >= 5) {
                $user->update(['locked_until' => now()->addMinutes(15)]);
            }

            // Tidak mencatat password ke log -> mencegah Sensitive Data Exposure.
            Log::warning('Login gagal', [
                'user_id' => $user->id,
                'email' => $email,
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
        });
    }

    private function registerSuccessfulLogin(User $user, Request $request): void
    {
        $user->update([
            'failed_login_attempts' => 0,
            'locked_until' => null,
            'last_login_at' => now(),
            'last_login_ip' => $request->ip(),
            'last_login_user_agent' => substr($request->userAgent() ?? 'Unknown', 0, 1000),
        ]);
    }
}
