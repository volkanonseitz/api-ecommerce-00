<?php

declare(strict_types=1);

namespace App\Modules\User\Services;

use App\Models\User;
use App\Modules\User\Actions\AttemptLoginAction;
use App\Modules\User\Actions\RegisterUserAction;
use App\Modules\User\DTO\RegisterUserData;
use Illuminate\Http\Request;

/**
 * AuthService kini hanya orchestrator tipis (sebelumnya "Fat Service"
 * berisi logika lockout, social-login, dsb). Logika berat dipindah ke
 * Action terpisah agar masing-masing punya satu tanggung jawab & mudah diuji.
 */
final class AuthService
{
    public function __construct(
        private readonly AttemptLoginAction $attemptLoginAction,
        private readonly RegisterUserAction $registerUserAction,
        private readonly SocialLoginService $socialLoginService,
    ) {}

    /**
     * @return array{status:string, user?:User, locked_until?:\Illuminate\Support\Carbon}
     */
    public function attemptLogin(string $email, string $password, Request $request): array
    {
        return $this->attemptLoginAction->execute($email, $password, $request);
    }

    public function register(RegisterUserData $data): User
    {
        return $this->registerUserAction->execute($data);
    }

    public function socialLogin(string $provider, string $accessToken): User
    {
        return $this->socialLoginService->handle($provider, $accessToken);
    }

    public function logout(User $user): bool
    {
        $token = $user->currentAccessToken();

        return $token ? (bool) $token->delete() : false;
    }

    public function issueToken(User $user, ?string $deviceName = null): string
    {
        $name = substr($deviceName ?: 'auth_token', 0, 255);

        return $user->createToken($name)->plainTextToken;
    }
}
