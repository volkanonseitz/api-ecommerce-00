<?php

declare(strict_types=1);

namespace App\Modules\User\Http\Controllers;

use App\Http\Controllers\BaseController;
use App\Modules\User\DTO\UpdateUserData;
use App\Modules\User\Http\Requests\ChangePasswordRequest;
use App\Modules\User\Http\Requests\UserUpdateRequest;
use App\Modules\User\Http\Resources\UserResource;
use App\Modules\User\Services\UserCommandService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

final class ProfileController extends BaseController
{
    public function __construct(private readonly UserCommandService $userCommandService) {}

    public function me(Request $request): JsonResponse
    {
        $user = $request->user();
        if (! $user) {
            throw new AuthorizationException(config('notice.NOT_AUTHORIZED'));
        }

        // Eager loading eksplisit -> mencegah N+1 saat relasi diakses di Resource/response.
        $user->load(['profile', 'wallet', 'address', 'shops.balance', 'managed_shop.balance'])
            ->loadLastOrder();

        return $this->sendSuccess(new UserResource($user), 'Profile data');
    }

    public function update(UserUpdateRequest $request): JsonResponse
    {
        // Selalu update diri sendiri ($request->user()) -> route param {id} TIDAK dipakai
        // sebagai sumber kebenaran, sehingga endpoint ini tidak rentan IDOR sama sekali.
        $user = $request->user();
        $data = UpdateUserData::fromValidated($request->validated());
        $updated = $this->userCommandService->updateSelf($user, $data);

        return $this->sendSuccess(new UserResource($updated), 'User updated');
    }

    public function changePassword(ChangePasswordRequest $request, \App\Modules\User\Actions\ChangePasswordAction $action): JsonResponse
    {
        $user = $request->user();
        $success = $action->execute($user, $request->validated('old_password'), $request->validated('new_password'));

        if (! $success) {
            return $this->sendError('Password lama tidak sesuai.', 400);
        }

        return $this->sendSuccess(null, 'Password changed');
    }

    public function updateEmail(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => ['required', 'email:rfc,dns', 'unique:users,email'],
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation failed', 422, $validator->errors());
        }

        $this->userCommandService->updateEmail($request->user(), $validator->validated()['email']);

        return $this->sendSuccess(null, 'Email updated, please verify your new email');
    }
}
