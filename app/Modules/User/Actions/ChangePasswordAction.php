<?php

declare(strict_types=1);

namespace App\Modules\User\Actions;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

final class ChangePasswordAction
{
    public function execute(User $user, string $oldPassword, string $newPassword): bool
    {
        if (! Hash::check($oldPassword, $user->password)) {
            return false;
        }

        $user->update(['password' => Hash::make($newPassword)]);

        // Revoke semua token aktif lain setelah ganti password (keamanan akun).
        $user->tokens()->where('id', '!=', $user->currentAccessToken()?->id)->delete();

        return true;
    }
}
