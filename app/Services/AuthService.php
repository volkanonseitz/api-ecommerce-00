<?php

namespace App\Services;

use App\Models\User;
use App\Models\Provider;
use App\DTO\UserData;
use App\Enums\Permission;
use App\Enums\Role;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Auth\Events\Registered;
use App\Models\Settings;

class AuthService
{
    public function __construct(
        private UserService $userService,
        private WalletService $walletService
    ) {}

    public function attemptLogin(string $email, string $password, bool $appValid = true): ?array
    {
        $user = User::where('email', $email)->where('is_active', true)->first();
        if (!$user || !Hash::check($password, $user->password) || !$appValid) {
            return null;
        }

        return [
            'token' => $user->createToken('auth_token')->plainTextToken,
            'permissions' => $user->getPermissionNames(),
            'email_verified' => $user->hasVerifiedEmail(),
            'role' => $user->getRoleNames()->first(),
            'user' => $user,
        ];
    }

    public function register(UserData $data, bool $mustVerifyEmail = false): array
    {
        $user = $this->userService->createUser($data);

        if ($mustVerifyEmail) {
            event(new Registered($user));
        }

        return [
            'token' => $user->createToken('auth_token')->plainTextToken,
            'permissions' => $user->getPermissionNames(),
            'role' => $user->getRoleNames()->first(),
            'user' => $user,
        ];
    }

    public function socialLogin(string $provider, string $accessToken): array
    {
        $this->validateProvider($provider);

        $socialUser = Socialite::driver($provider)->userFromToken($accessToken);
        $userExist = User::where('email', $socialUser->getEmail())->exists();

        $user = User::firstOrCreate(
            ['email' => $socialUser->getEmail()],
            [
                'email_verified_at' => now(),
                'name' => $socialUser->getName(),
            ]
        );

        $user->providers()->updateOrCreate(
            ['provider' => $provider, 'provider_user_id' => $socialUser->getId()],
            []
        );

        $avatar = ['thumbnail' => $socialUser->getAvatar(), 'original' => $socialUser->getAvatar()];
        $user->profile()->updateOrCreate([], ['avatar' => $avatar]);

        if (!$user->hasPermissionTo(Permission::CUSTOMER->value)) {
            $user->givePermissionTo(Permission::CUSTOMER->value);
            $user->assignRole(Role::CUSTOMER->value);
        }

        if (!$userExist) {
            $this->walletService->addPoints($user->id, Settings::getData()->options['signupPoints'] ?? 0);
        }

        return [
            'token' => $user->createToken('auth_token')->plainTextToken,
            'permissions' => $user->getPermissionNames(),
            'role' => $user->getRoleNames()->first(),
            'user' => $user,
        ];
    }

    private function validateProvider(string $provider): void
    {
        if (!in_array($provider, ['facebook', 'google'])) {
            throw new \Exception(config('notice.PLEASE_LOGIN_USING_FACEBOOK_OR_GOOGLE'));
        }
    }

    public function logout(User $user): bool
    {
        $token = $user->currentAccessToken();
        if ($token) {
            $token->delete();
            return true;
        }
        return false;
    }
}