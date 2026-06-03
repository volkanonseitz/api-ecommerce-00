<?php

namespace App\Actions;

use App\Models\User;
use App\Models\Address;
use App\Models\Profile;
use App\DTO\UserData;

class UpdateUserAction
{
    public function execute(User $user, UserData $data): User
    {
        if ($data->address) {
            foreach ($data->address as $address) {
                if (isset($address['id'])) {
                    Address::where('id', $address['id'])->where('customer_id', $user->id)->update($address);
                } else {
                    $user->address()->create($address);
                }
            }
        }

        if ($data->profile) {
            if (isset($data->profile['id'])) {
                Profile::where('id', $data->profile['id'])->where('customer_id', $user->id)->update($data->profile);
            } else {
                $user->profile()->create($data->profile);
            }
        }

        $updateData = [];
        if ($data->name) $updateData['name'] = $data->name;
        if ($data->email) $updateData['email'] = $data->email;
        if ($data->shop_id !== null) $updateData['shop_id'] = $data->shop_id;
        if (!empty($updateData)) {
            $user->update($updateData);
        }

        return $user->fresh(['profile', 'address', 'shops', 'managed_shop']);
    }
}