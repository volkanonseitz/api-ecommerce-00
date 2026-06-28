<?php

declare(strict_types=1);

namespace App\Modules\Address\Actions;

use App\Models\Address;
use App\Models\User;
use App\Modules\Address\DTO\AddressData;
use Illuminate\Support\Facades\DB;

final class CreateAddressAction
{
    public function execute(User $user, AddressData $data): Address
    {
        return DB::transaction(function () use ($user, $data) {
            // Jika alamat baru di-set sebagai default, unset default yang lama
            if ($data->default) {
                Address::where('customer_id', $user->id)->update(['default' => false]);
            }

            return Address::create([
                ...$data->toArray(),
                'customer_id' => $user->id,
            ]);
        });
    }
}
