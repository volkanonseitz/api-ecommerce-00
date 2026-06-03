<?php

namespace App\Actions;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

class ChangePasswordAction
{
    public function execute(User $user, string $oldPassword, string $newPassword): bool
    {
        if (!Hash::check($oldPassword, $user->password)) {
            return false;
        }
        $user->password = Hash::make($newPassword);
        $user->save();
        return true;
    }
}