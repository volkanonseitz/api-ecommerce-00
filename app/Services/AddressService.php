<?php

namespace App\Services;

use App\Models\Address;
use App\DTO\AddressData;
use Illuminate\Contracts\Auth\Authenticatable;

class AddressService
{
    /**
     * Ambil semua address dengan relasi customer
     */
    public function getAll()
    {
        return Address::with('customer')->get();
    }

    /**
     * Find address by id
     */
    public function findOrFail(int $id): Address
    {
        return Address::with('customer')->findOrFail($id);
    }

    /**
     * Create address
     */
    public function create(AddressData $data): Address
    {
        return Address::create($data->toArray());
    }

    /**
     * Update address
     */
    public function update(Address $address, AddressData $data): Address
    {
        $address->update($data->toArray());
        return $address->fresh();
    }

    /**
     * Delete address (dengan permission check)
     */
    public function delete(Address $address, Authenticatable $user): bool
    {
        // Super admin bisa hapus semua
        if ($user->hasPermissionTo('super_admin')) {
            return $address->delete();
        }
        // Customer hanya bisa hapus milik sendiri
        if ($address->customer_id == $user->id) {
            return $address->delete();
        }
        return false;
    }
}