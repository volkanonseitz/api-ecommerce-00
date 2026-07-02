<?php

declare(strict_types=1);

namespace App\Modules\User\Http\Controllers;

use App\Http\Controllers\BaseController;
use App\Models\User;
use App\Modules\User\Actions\ToggleAdminPrivilegeAction;
use App\Modules\User\Actions\ToggleUserActiveAction;
use App\Modules\User\Http\Requests\AdminCreateUserRequest;
use App\Modules\User\Http\Requests\AdminUpdateUserRequest;
use App\Modules\User\Http\Resources\UserResource;
use App\Modules\User\Services\UserCommandService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Semua endpoint di controller ini dilindungi middleware
 * `permission:super_admin` di route, DAN dicek ulang lewat Policy
 * di FormRequest::authorize() / Gate::authorize (defense in depth).
 */
final class UserManagementController extends BaseController
{
    public function __construct(private readonly UserCommandService $userCommandService) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', User::class);

        $limit = (int) ($request->integer('limit') ?: 15);
        // Eager loading wajib -> mencegah N+1 ketika UserResource mengakses relasi profile/address.
        $users = User::with(['profile', 'address'])->paginate($limit);

        return $this->sendPaginated($users, UserResource::collection($users->getCollection()), 'Daftar user berhasil diambil.');
    }

    public function show(int $id): JsonResponse
    {
        $user = User::with(['profile', 'address', 'shops', 'managed_shop'])->findOrFail($id);
        $this->authorize('view', $user);

        return $this->sendSuccess(new UserResource($user), 'User detail');
    }

    public function store(AdminCreateUserRequest $request): JsonResponse
    {
        $user = $this->userCommandService->createByAdmin($request->validated());

        return $this->sendSuccess(new UserResource($user), 'User created', 201);
    }

    public function update(AdminUpdateUserRequest $request, int $id): JsonResponse
    {
        $user = User::findOrFail($id);
        // AdminUpdateUserRequest::authorize() sudah memanggil Policy::update,
        // baris ini adalah lapisan kedua (berlapis) untuk berjaga-jaga.
        $this->authorize('update', $user);

        $updated = $this->userCommandService->updateByAdmin($user, $request->validated());

        return $this->sendSuccess(new UserResource($updated), 'User updated');
    }

    public function destroy(int $id): JsonResponse
    {
        $user = User::findOrFail($id);
        $this->authorize('delete', $user);

        $user->delete();

        return $this->sendSuccess(null, 'User deleted');
    }

    public function ban(Request $request, ToggleUserActiveAction $action): JsonResponse
    {
        $target = User::findOrFail($request->integer('id'));
        $this->authorize('toggleActive', $target);

        $banned = $action->execute($target, active: false);

        return $this->sendSuccess(new UserResource($banned), 'User banned');
    }

    public function activate(Request $request, ToggleUserActiveAction $action): JsonResponse
    {
        $target = User::findOrFail($request->integer('id'));
        $this->authorize('toggleActive', $target);

        $activated = $action->execute($target, active: true);

        return $this->sendSuccess(new UserResource($activated), 'User activated');
    }

    public function toggleAdmin(Request $request, ToggleAdminPrivilegeAction $action): JsonResponse
    {
        $target = User::findOrFail($request->integer('user_id'));
        $this->authorize('toggleAdmin', $target);

        $isNowAdmin = $action->execute($target);

        return $this->sendSuccess(true, $isNowAdmin ? 'Admin granted' : 'Admin revoked');
    }
}
