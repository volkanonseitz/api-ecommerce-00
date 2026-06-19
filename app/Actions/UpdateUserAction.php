<?php

namespace App\Actions;

use App\DTO\UserData;
use App\Models\Address;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class UpdateUserAction
{
    public function execute(User $user, UserData $data): User
    {
        return DB::transaction(function () use ($user, $data) {

            if ($data->address) {
                foreach ($data->address as $address) {

                    $payload = Arr::only($address, [
                        'title',
                        'type',
                        'address',
                        'city',
                        'state',
                        'zip',
                        'country',
                        'phone',
                        'is_default',
                    ]);

                    if (isset($address['id'])) {
                        Address::query()
                            ->where('id', $address['id'])
                            ->where('customer_id', $user->id)
                            ->update($payload);
                    } else {
                        $user->address()->create($payload);
                    }
                }
            }

            if ($data->profile) {
                $payload = Arr::only($data->profile, [
                    'bio',
                    'avatar',
                    'contact',
                    'gender',
                    'birth_date',
                ]);

                if (isset($data->profile['id'])) {
                    Profile::query()
                        ->where('id', $data->profile['id'])
                        ->where('customer_id', $user->id)
                        ->update($payload);
                } else {
                    $user->profile()->create($payload);
                }
            }

            $updateData = [];

            if ($data->name) {
                $updateData['name'] = $data->name;
            }

            if ($data->email) {
                $updateData['email'] = $data->email;
            }

            if ($data->shop_id !== null) {
                $updateData['shop_id'] = $data->shop_id;
            }

            if (! empty($updateData)) {
                $user->update($updateData);
            }

            return $user->fresh([
                'profile',
                'address',
                'shops',
                'managed_shop',
            ]);
        });
    }
}
