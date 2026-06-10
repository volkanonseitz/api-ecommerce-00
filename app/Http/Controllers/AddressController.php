<?php

namespace App\Http\Controllers;

use App\Services\AddressService;
use App\Http\Requests\AddressRequest;
use App\Http\Resources\AddressResource;
use App\DTO\AddressData;
use App\Models\Address;
use App\Enums\Permission;
use Illuminate\Http\Request;

class AddressController extends Controller
{
    public function __construct(private AddressService $addressService) {}

    /**
     * GET /addresses
     */
    public function index()
    {
        $addresses = $this->addressService->getAll();
        return AddressResource::collection($addresses);
    }

    /**
     * POST /addresses
     */
    public function store(AddressRequest $request)
    {
        $data = AddressData::fromRequest($request->validated());
        $address = $this->addressService->create($data);
        return new AddressResource($address);
    }

    /**
     * GET /addresses/{id}
     */
    public function show($id)
    {
        $address = $this->addressService->findOrFail($id);
        return new AddressResource($address);
    }

    /**
     * PUT /addresses/{id}
     */
    public function update(AddressRequest $request, $id)
    {
        $address = Address::findOrFail($id);
        $data = AddressData::fromRequest($request->validated());
        $updated = $this->addressService->update($address, $data);
        return new AddressResource($updated);
    }

    /**
     * DELETE /addresses/{id}
     */
    public function destroy(Request $request, $id)
    {
        $address = Address::findOrFail($id);
        $deleted = $this->addressService->delete($address, $request->user());
        if (!$deleted) {
            abort(403, config('notice.NOT_AUTHORIZED'));
        }
        return response()->json(['message' => 'Address deleted successfully']);
    }
}