<?php

namespace App\Http\Controllers;

use App\Services\OwnershipTransferService;
use App\Http\Requests\TransferShopOwnerShipRequest;
use App\Http\Resources\OwnershipTransferResource;
use App\DTO\OwnershipTransferData;
use Illuminate\Http\Request;
use Illuminate\Auth\Access\AuthorizationException;

class OwnershipTransferController extends Controller
{
    public function __construct(private OwnershipTransferService $transferService) {}

    /**
     * GET /ownership-transfers
     */
    public function index(Request $request)
    {
        $user = $request->user();
        if (!$user) throw new AuthorizationException(config('constants.NOT_AUTHORIZED'));

        $limit = $request->limit ?? 15;
        $histories = $this->transferService->getTransferHistoriesQuery($request, $user)->paginate($limit);
        $data = OwnershipTransferResource::collection($histories)->response()->getData(true);
        return formatAPIResourcePaginate($data);
    }

    /**
     * POST /ownership-transfers (store)
     * Digunakan untuk membuat request transfer (biasanya dari ShopController, tapi endpoint ini mungkin ada)
     * Saya asumsikan endpoint ini tidak digunakan langsung (transfer dibuat via ShopController),
     * namun tetap kita buat untuk kelengkapan.
     */
    public function store(TransferShopOwnerShipRequest $request)
    {
        $user = $request->user();
        if (!$this->transferService->hasPermission($user, $request->shop_id)) {
            throw new AuthorizationException(config('constants.NOT_AUTHORIZED'));
        }

        $data = OwnershipTransferData::fromRequest($request->validated(), $user->id);
        $transfer = $this->transferService->createTransfer($data);
        return new OwnershipTransferResource($transfer);
    }

    /**
     * GET /ownership-transfers/{transaction_identifier}
     */
    public function show(Request $request, $transaction_identifier)
    {
        $transfer = $this->transferService->getTransferDetail($transaction_identifier, $request->request_view_type);
        return new OwnershipTransferResource($transfer);
    }

    /**
     * PUT /ownership-transfers/{id} (update status)
     */
    public function update(Request $request, $id)
    {
        $user = $request->user();
        if (!$user) throw new AuthorizationException(config('constants.NOT_AUTHORIZED'));

        $request->validate(['status' => 'required|string|in:pending,approved,rejected']);
        $transfer = $this->transferService->updateTransferStatus((int)$id, $request->status, $user);
        return new OwnershipTransferResource($transfer);
    }

    /**
     * DELETE /ownership-transfers/{id}
     */
    public function destroy(Request $request, $id)
    {
        $user = $request->user();
        if (!$user) throw new AuthorizationException(config('constants.NOT_AUTHORIZED'));

        $this->transferService->deleteTransfer((int)$id, $user);
        return response()->json(['message' => 'Transfer record deleted']);
    }
}