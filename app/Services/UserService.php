<?php

namespace App\Services;

use App\Models\User;
use App\Models\Shop;
use App\Models\Wallet;
use App\Models\Settings;
use App\Enums\Permission;
use App\Enums\Role;
use App\Actions\CreateUserAction;
use App\Actions\UpdateUserAction;
use App\DTO\UserData;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use App\Mail\ForgetPassword;
use Illuminate\Support\Str;
use Carbon\Carbon;

class UserService
{
    public function __construct(
        private CreateUserAction $createUser,
        private UpdateUserAction $updateUser,
        private WalletService $walletService
    ) {}

    public function getAdminUsers()
    {
        return Cache::remember('cached_admin', 900, function () {
            return User::with('profile')
                ->where('is_active', true)
                ->whereHas('permissions', fn($q) => $q->where('name', Permission::SUPER_ADMIN->value))
                ->get();
        });
    }

    public function hasPermission(?Authenticatable $user, ?int $shopId = null): bool
    {
        if (!$user) return false;
        if ($user->hasPermissionTo(Permission::SUPER_ADMIN->value)) return true;

        if (!$shopId) return false;

        $shop = Shop::find($shopId);
        if (!$shop || !$shop->is_active) {
            throw new \Exception(config('notice.SHOP_NOT_APPROVED'));
        }

        if ($user->hasPermissionTo(Permission::STORE_OWNER->value)) {
            return $shop->owner_id === $user->id;
        }

        if ($user->hasPermissionTo(Permission::STAFF->value)) {
            return $shop->staffs->contains($user->id);
        }

        return false;
    }

    public function createUser(UserData $data): User
    {
        return $this->createUser->execute($data);
    }

    public function updateUser(User $user, UserData $data): User
    {
        return $this->updateUser->execute($user, $data);
    }

    public function sendResetEmail(string $email, string $token): bool
    {
        try {
            Mail::to($email)->send(new ForgetPassword($token));
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function updateEmail(User $user, string $newEmail): array
    {
        $user->email = $newEmail;
        $user->email_verified_at = null;
        $user->save();
        $user->sendEmailVerificationNotification();
        return ['message' => config('notice.EMAIL_UPDATED_SUCCESSFULLY'), 'status' => 'success'];
    }

    public function giveSignupPoints(int $userId): void
    {
        $settings = Settings::getData();
        $points = $settings->options['signupPoints'] ?? 0;
        $this->walletService->addPoints($userId, $points);
    }
}