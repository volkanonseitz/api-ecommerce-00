<?php

declare(strict_types=1);

namespace App\Modules\OwnershipTransfer\Http\Controllers;

use App\Http\Controllers\BaseController;
use App\Models\OwnershipTransfer;
use App\Modules\OwnershipTransfer\DTO\OwnershipTransferData;
use App\Modules\OwnershipTransfer\Http\Requests\OwnershipTransferRequest;
use App\Modules\OwnershipTransfer\Http\Resources\OwnershipTransferResource;
use App\Modules\OwnershipTransfer\Services\OwnershipTransferService;
use Illuminate\Http\Request;

class OwnershipTransferController extends BaseController
{
    public function __construct(private OwnershipTransferService $transferService) {}

    /**
     * GET /ownership-transfers
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $this->authorize('viewAny', OwnershipTransfer::class);

        $limit = (int) ($request->limit ?? 15);
        $histories = $this->transferService->getTransferHistoriesQuery($request, $user)->paginate($limit);

        return OwnershipTransferResource::collection($histories);
    }

    /**
     * POST /ownership-transfers
     */
    public function store(OwnershipTransferRequest $request)
    {
        $this->authorize('create', OwnershipTransfer::class);

        $user = $request->user();

        // Check shop ownership permission
        if (! $this->transferService->hasPermission($user, $request->shop_id)) {
            return $this->sendError(config('notice.NOT_AUTHORIZED'), 403);
        }

        $data = OwnershipTransferData::fromRequest($request->validated(), $user->id);
        $transfer = $this->transferService->createTransfer($data);

        return new OwnershipTransferResource($transfer);
    }

    /**
     * GET /ownership-transfers/{transaction_identifier}
     */
    public function show(Request $request, string $transaction_identifier)
    {
        $transfer = $this->transferService->getTransferDetail($transaction_identifier, $request->request_view_type);
        $this->authorize('view', $transfer);

        return new OwnershipTransferResource($transfer);
    }

    /**
     * PUT /ownership-transfers/{id}
     */
    public function update(Request $request, int $id)
    {
        $transfer = OwnershipTransfer::findOrFail($id);
        $this->authorize('update', $transfer);

        $request->validate([
            'status' => 'required|string|in:pending,approved,rejected',
        ]);

        $user = $request->user();
        $updated = $this->transferService->updateTransferStatus($id, $request->status, $user);

        return new OwnershipTransferResource($updated);
    }

    /**
     * DELETE /ownership-transfers/{id}
     */
    public function destroy(Request $request, int $id)
    {
        $transfer = OwnershipTransfer::findOrFail($id);
        $this->authorize('delete', $transfer);

        $this->transferService->deleteTransfer($id, $request->user());

        return $this->sendSuccess(null, 'Transfer record deleted successfully');
    }
}
