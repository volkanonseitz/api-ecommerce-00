<?php

declare(strict_types=1);

namespace App\Modules\User\Actions;

use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * Khusus dipakai oleh admin (UserManagementController::store).
 * Berbeda dari RegisterUserAction karena di sini shop_id BOLEH di-set,
 * tapi hanya karena AdminCreateUserRequest sudah memvalidasi & mengotorisasi caller-nya.
 */
final class CreateUserAction
{
    /**
     * @param  array{name:string,email:string,password:string,shop_id?:?int,profile?:?array,address?:?array}  $validated
     */
    public function execute(array $validated): User
    {
        return DB::transaction(function () use ($validated): User {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'shop_id' => $validated['shop_id'] ?? null,
            ]);

            if (! empty($validated['profile'])) {
                $user->profile()->create(Arr::only($validated['profile'], ['avatar', 'bio', 'socials']));
            }

            if (! empty($validated['address'])) {
                $user->address()->create(Arr::only(
                    $validated['address'],
                    ['street_address', 'city', 'state', 'zip', 'country']
                ));
            }

            return $user->fresh(['profile', 'address']);
        });
    }
}
