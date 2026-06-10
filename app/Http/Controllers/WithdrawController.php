<?php

namespace App\Http\Controllers;

use App\Services\WithdrawService;
use App\Http\Requests\WithdrawRequest;
use App\Http\Requests\UpdateWithdrawRequest;
use App\Http\Resources\WithdrawResource;
use App\DTO\WithdrawData;
use App\Enums\WithdrawStatus;
use Illuminate\Http\Request;
use Illuminate\Auth\Access\AuthorizationException;
use Symfony\Component\HttpKernel\Exception\HttpException;

class WithdrawController extends Controller
{
    public function __construct(private WithdrawService $withdrawService) {}

    /**
     * GET /withdraws
     */
    public function index(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            throw new AuthorizationException(config('constants.NOT_AUTHORIZED'));
        }
        $limit = $request->limit ?? 15;
        $withdraws = $this->withdrawService->getWithdrawsQuery($request, $user)->paginate($limit);
        return WithdrawResource::collection($withdraws);
    }

    /**
     * POST /withdraws
     */
    public function store(WithdrawRequest $request)
    {
        $user = $request->user();
        if (!$user) {
            throw new AuthorizationException(config('constants.NOT_AUTHORIZED'));
        }
        $data = WithdrawData::fromRequest($request->validated());
        $withdraw = $this->withdrawService->createWithdraw($data, $user);
        return new WithdrawResource($withdraw);
    }

    /**
     * GET /withdraws/{id}
     */
    public function show(Request $request, $id)
    {
        $user = $request->user();
        if (!$user) {
            throw new AuthorizationException(config('constants.NOT_AUTHORIZED'));
        }
        $withdraw = $this->withdrawService->findWithdraw((int)$id, $user);
        return new WithdrawResource($withdraw);
    }

    /**
     * PUT /withdraws/{id} (not allowed)
     */
    public function update(UpdateWithdrawRequest $request, $id)
    {
        throw new HttpException(400, config('constants.ACTION_NOT_VALID'));
    }

    /**
     * DELETE /withdraws/{id}
     */
    public function destroy(Request $request, $id)
    {
        $user = $request->user();
        if (!$user) {
            throw new AuthorizationException(config('constants.NOT_AUTHORIZED'));
        }
        $this->withdrawService->deleteWithdraw((int)$id, $user);
        return response()->json(['message' => 'Withdraw deleted']);
    }

    /**
     * POST /withdraws/approve
     */
    public function approveWithdraw(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            throw new AuthorizationException(config('constants.NOT_AUTHORIZED'));
        }
        $request->validate([
            'id' => 'required|exists:withdraws,id',
            'status' => 'required|string|in:' . implode(',', WithdrawStatus::values()),
        ]);
        $withdraw = $this->withdrawService->approveWithdraw($request->id, $request->status, $user);
        return new WithdrawResource($withdraw);
    }
}