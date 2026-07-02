<?php

namespace App\Modules\Withdraw\Http\Controllers;

use App\Enums\WithdrawStatus;
use App\Http\Controllers\BaseController;
use App\Models\Withdraw;
use App\Modules\Withdraw\Actions\ApproveWithdrawAction;
use App\Modules\Withdraw\Actions\CreateWithdrawAction;
use App\Modules\Withdraw\Actions\DeleteWithdrawAction;
use App\Modules\Withdraw\DTO\WithdrawData;
use App\Modules\Withdraw\Http\Requests\WithdrawRequest;
use App\Modules\Withdraw\Http\Requests\WithdrawUpdateRequest;
use App\Modules\Withdraw\Http\Resources\WithdrawResource;
use App\Modules\Withdraw\Services\WithdrawQueryService;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

class WithdrawController extends BaseController
{
    public function __construct(
        private readonly WithdrawQueryService $queryService,
        private readonly CreateWithdrawAction $createAction,
        private readonly DeleteWithdrawAction $deleteAction,
        private readonly ApproveWithdrawAction $approveAction,
    ) {}

    /**
     * GET /withdraws
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', Withdraw::class);

        $limit = $request->limit ?? 15;
        $withdraws = $this->queryService->getWithdrawsQuery($request, $request->user())->paginate($limit);

        return WithdrawResource::collection($withdraws);
    }

    /**
     * POST /withdraws
     */
    public function store(WithdrawRequest $request)
    {
        $this->authorize('create', Withdraw::class);

        $data = WithdrawData::fromRequest($request->validated());
        $withdraw = $this->createAction->execute($data, $request->user());

        return new WithdrawResource($withdraw);
    }

    /**
     * GET /withdraws/{id}
     */
    public function show(Request $request, int $id)
    {
        $withdraw = $this->queryService->findWithdraw($id, $request->user());
        $this->authorize('view', $withdraw);

        return new WithdrawResource($withdraw);
    }

    /**
     * PUT /withdraws/{id} (not allowed)
     */
    public function update(WithdrawUpdateRequest $request, $id)
    {
        throw new HttpException(400, config('notice.ACTION_NOT_VALID'));
    }

    /**
     * DELETE /withdraws/{id}
     */
    public function destroy(Request $request, int $id)
    {
        $withdraw = $this->queryService->findWithdraw($id, $request->user());
        $this->authorize('delete', $withdraw);

        $this->deleteAction->execute($withdraw, $request->user());

        return $this->sendSuccess(null, 'Withdraw deleted');
    }

    /**
     * POST /withdraws/approve
     */
    public function approveWithdraw(Request $request)
    {
        $this->authorize('approve', Withdraw::class);
        $request->validate([
            'id' => 'required|exists:withdraws,id',
            'status' => 'required|string|in:'.implode(',', WithdrawStatus::getValues()),
        ]);

        $withdraw = $this->queryService->findOrFail((int) $request->id);
        $updatedWithdraw = $this->approveAction->execute($withdraw, $request->status, $request->user());

        return new WithdrawResource($updatedWithdraw);
    }
}
