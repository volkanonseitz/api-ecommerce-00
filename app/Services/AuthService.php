<?php

namespace App\Services;

use App\DTO\UserData;
use App\Enums\Permission;
use App\Enums\Role;
use App\Models\Settings;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Auth\Events\Registered;
use Laravel\Socialite\Facades\Socialite;

class AuthService
{
    public function __construct(
        private UserService $userService,
        private WalletService $walletService
    ) {}

    public function attemptLogin(
        string $email,
        string $password,
        Request $request,
        bool $appValid = true
    ): ?array {
        $user = User::where('email', $email)->first();

        if (! $user) {
            usleep(random_int(300000, 700000));

            return null;
        }

        if ($user->locked_until && $user->locked_until->isFuture()) {
            return [
                'locked' => true,
                'locked_until' => $user->locked_until,
            ];
        }

        if (
            ! $user->is_active ||
            ! Hash::check($password, $user->password) ||
            ! $appValid
        ) {
            $user->increment('failed_login_attempts');

            if ($user->failed_login_attempts >= 5) {
                $user->update([
                    'locked_until' => now()->addMinutes(15),
                ]);
            }

            Log::warning('Login gagal', [
                'user_id' => $user->id,
                'email' => $email,
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            return null;
        }

        if (! $user->hasVerifiedEmail()) {
            return [
                'email_verified' => false,
            ];
        }

        $user->update([
            'failed_login_attempts' => 0,
            'locked_until' => null,
            'last_login_at' => now(),
            'last_login_ip' => $request->ip(),
        ]);

        $user->tokens()->delete();

        return [
            'token' => $user->createToken('auth_token')->plainTextToken,
            'permissions' => $user->getPermissionNames(),
            'email_verified' => true,
            'role' => $user->getRoleNames()->first(),
        ];
    }

    public function register(UserData $data, bool $mustVerifyEmail = false): array
    {
        $user = $this->userService->createUser($data);

        if ($mustVerifyEmail) {
            event(new Registered($user));
        }

        $user->tokens()->delete();

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

        $email = $socialUser->getEmail();

        if (! $email) {
            throw new \Exception('Email not provided by social provider');
        }

        $userExist = User::where('email', $email)->exists();

        $user = User::firstOrCreate(
            ['email' => $email],
            [
                'email_verified_at' => now(),
                'name' => $socialUser->getName() ?? 'User',
            ]
        );

        $user->providers()->updateOrCreate(
            [
                'provider' => $provider,
                'provider_user_id' => $socialUser->getId(),
            ],
            []
        );

        $avatar = [
            'thumbnail' => $socialUser->getAvatar(),
            'original' => $socialUser->getAvatar(),
        ];

        $user->profile()->updateOrCreate(
            [],
            ['avatar' => $avatar]
        );

        if (
            ! $user->hasAnyRole([
                Role::SUPER_ADMIN->value,
                Role::STORE_OWNER->value,
                Role::STAFF->value,
            ])
        ) {
            if (! $user->hasPermissionTo(Permission::CUSTOMER->value)) {
                $user->givePermissionTo(Permission::CUSTOMER->value);
            }

            if (! $user->hasRole(Role::CUSTOMER->value)) {
                $user->assignRole(Role::CUSTOMER->value);
            }
        }

        if (! $userExist) {
            $settings = Settings::getData();

            $signupPoints = data_get(
                $settings,
                'options.signupPoints',
                0
            );

            if ($signupPoints > 0) {
                $this->walletService->addPoints(
                    $user->id,
                    (int) $signupPoints
                );
            }
        }

        $user->tokens()->delete();

        return [
            'token' => $user->createToken('auth_token')->plainTextToken,
            'permissions' => $user->getPermissionNames(),
            'role' => $user->getRoleNames()->first(),
            'user' => $user,
        ];
    }

    private function validateProvider(string $provider): void
    {
        if (! in_array($provider, ['facebook', 'google'])) {
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
