<?php

namespace App\Http\Controllers;

use App\DTO\AddressData;
use App\Http\Requests\AddressRequest;
use App\Http\Resources\AddressResource;
use App\Models\Address;
use App\Services\AddressService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class AddressController extends BaseController
{
    use AuthorizesRequests;
    public function __construct(
        private readonly AddressService $addressService
    ) {}

    public function index(Request $request)
    {
        $addresses = $this->addressService->getUserAddresses(
            user: $request->user(),
            perPage: min($request->integer('per_page', 15), 100)
        );

        return AddressResource::collection($addresses);
    }

    public function store(AddressRequest $request)
    {
        $address = $this->addressService->create(
            $request->user(),
            AddressData::fromRequest($request->validated())
        );

        return (new AddressResource($address))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function show(Address $address)
    {
        $this->authorize('view', $address);

        return new AddressResource($address);
    }

    public function update(AddressRequest $request, Address $address)
    {
        $this->authorize('update', $address);

        $address = $this->addressService->update(
            $address,
            AddressData::fromRequest($request->validated())
        );

        return new AddressResource($address);
    }

    public function destroy(Address $address)
    {
        $this->authorize('delete', $address);

        $this->addressService->delete($address);

        return response()->json([
            'success' => true,
            'message' => 'Address deleted successfully.',
        ]);
    }
}
