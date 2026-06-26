<?php

declare(strict_types=1);

namespace App\Services;

use App\DTO\AddressData;
use App\Models\Address;
use App\Models\User;
use App\Traits\AuthorizesShopAccess;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class AddressService
{
    use AuthorizesShopAccess;

    public function getUserAddresses(User $user, int $perPage = 15): LengthAwarePaginator
    {
        return Address::query()
            ->where('customer_id', $user->id)
            ->latest()
            ->paginate($perPage);
    }

    public function create(User $user, AddressData $data): Address
    {
        return DB::transaction(function () use ($user, $data) {
            if ($data->default) {
                Address::where('customer_id', $user->id)->update(['default' => false]);
            }

            return Address::create([
                ...$data->toArray(),
                'customer_id' => $user->id,
            ]);
        });
    }

    public function update(Address $address, AddressData $data): Address
    {
        if ($address->customer_id !== auth()->id()) {
            abort(403, config('notice.NOT_AUTHORIZED'));
        }

        return DB::transaction(function () use ($address, $data) {
            if ($data->default) {
                Address::where('customer_id', $address->customer_id)
                    ->where('id', '!=', $address->id)
                    ->update(['default' => false]);
            }

            $address->update($data->toArray());
            return $address->fresh();
        });
    }

    public function delete(Address $address): bool
    {
        if ($address->customer_id !== auth()->id()) {
            abort(403, config('notice.NOT_AUTHORIZED'));
        }
        return $address->delete();
    }
}