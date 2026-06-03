<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Carbon\Carbon;

class PasswordService
{
    public function __construct(private UserService $userService) {}

    public function forgetPassword(string $email): array
    {
        $user = User::where('email', $email)->first();
        if (!$user) {
            return ['success' => false, 'message' => config('notice.NOT_FOUND')];
        }

        $tokenData = DB::table('password_resets')->where('email', $email)->first();
        if (!$tokenData) {
            $token = Str::random(16);
            DB::table('password_resets')->insert([
                'email' => $email,
                'token' => $token,
                'created_at' => Carbon::now(),
            ]);
            $tokenData = DB::table('password_resets')->where('email', $email)->first();
        }

        $sent = $this->userService->sendResetEmail($email, $tokenData->token);
        if ($sent) {
            return ['success' => true, 'message' => config('notice.CHECK_INBOX_FOR_PASSWORD_RESET_EMAIL')];
        } else {
            return ['success' => false, 'message' => config('notice.SOMETHING_WENT_WRONG')];
        }
    }

    public function verifyToken(string $email, string $token): array
    {
        $tokenData = DB::table('password_resets')->where('token', $token)->first();
        if (!$tokenData || $tokenData->email !== $email) {
            return ['success' => false, 'message' => config('notice.INVALID_TOKEN')];
        }
        return ['success' => true, 'message' => config('notice.TOKEN_IS_VALID')];
    }

    public function resetPassword(string $email, string $token, string $newPassword): array
    {
        $tokenData = DB::table('password_resets')
            ->where('email', $email)
            ->where('token', $token)
            ->first();

        if (!$tokenData) {
            return ['success' => false, 'message' => config('notice.INVALID_TOKEN')];
        }

        $user = User::where('email', $email)->first();
        if (!$user) {
            return ['success' => false, 'message' => config('notice.NOT_FOUND')];
        }

        $user->password = Hash::make($newPassword);
        $user->save();

        DB::table('password_resets')->where('email', $email)->delete();

        return ['success' => true, 'message' => config('notice.PASSWORD_RESET_SUCCESSFUL')];
    }

    public function changePassword(User $user, string $oldPassword, string $newPassword): array
    {
        if (!Hash::check($oldPassword, $user->password)) {
            return ['success' => false, 'message' => config('notice.OLD_PASSWORD_INCORRECT')];
        }
        $user->password = Hash::make($newPassword);
        $user->save();
        return ['success' => true, 'message' => config('notice.PASSWORD_RESET_SUCCESSFUL')];
    }
}