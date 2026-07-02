<?php

declare(strict_types=1);

namespace App\Modules\User\Http\Controllers;

use App\Enums\Permission;
use App\Events\ProcessUserData;
use App\Http\Controllers\BaseController;
use App\Modules\User\DTO\RegisterUserData;
use App\Modules\User\Http\Requests\LoginRequest;
use App\Modules\User\Http\Requests\RegisterRequest;
use App\Modules\User\Http\Requests\SocialLoginRequest;
use App\Modules\User\Services\AuthService;
use App\Modules\User\Services\UserSecurityService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Controller hanya mengarahkan lalu lintas data (thin controller):
 * validasi -> DTO -> Service/Action -> Resource/Response.
 * Tidak ada query Eloquent atau business logic langsung di sini.
 */
final class AuthController extends BaseController
{
    public function __construct(
        private readonly AuthService $authService,
        private readonly UserSecurityService $securityService
    ) {}

    public function login(LoginRequest $request): JsonResponse
    {
        // Rate limiting
        $ip = $request->ip();
        $email = $request->validated('email');

        if (! $this->securityService->enforceRateLimit($ip, $email)) {
            return $this->sendError('Terlalu banyak percobaan login. Silakan coba lagi nanti.', 429);
        }

        $result = $this->authService->attemptLogin(
            $email,
            $request->validated('password'),
            $request
        );

        return match ($result['status']) {
            'invalid' => $this->sendError('Email atau password tidak valid.', 401),
            'locked' => $this->sendError('Akun dikunci sementara.', 423, [
                'locked_until' => $result['locked_until'],
            ]),
            'unverified' => $this->sendError('Silakan verifikasi email Anda terlebih dahulu.', 403),
            'success' => $this->respondWithToken($result['user'], $request),
        };
    }

    private function respondWithToken($user, Request $request): JsonResponse
    {
        $token = $this->authService->issueToken($user, $request->userAgent());

        // Track session activity
        $this->securityService->trackSessionActivity($user, $request);

        event(new ProcessUserData);

        return $this->sendSuccess([
            'token' => $token,
            'permissions' => $user->getPermissionNames(),
            'email_verified' => true,
            'role' => $user->getRoleNames()->first(),
            'session_id' => $user->currentAccessToken()?->id,
        ], 'Login successful');
    }

    public function register(RegisterRequest $request): JsonResponse
    {
        // Otorisasi nilai `permission` yang DIMINTA dicek di sini (bukan trust dari payload
        // mentah), lalu diteruskan terpisah ke DTO -> defense in depth terhadap privilege escalation.
        $requestedPermission = $request->validated('permission');
        if ($requestedPermission === Permission::SUPER_ADMIN->value) {
            $requestedPermission = null;
        }

        $data = RegisterUserData::fromValidated($request->validated(), $requestedPermission);
        $user = $this->authService->register($data);

        $token = $this->authService->issueToken($user, 'register');
        event(new ProcessUserData);

        return $this->sendSuccess([
            'token' => $token,
            'permissions' => $user->getPermissionNames(),
            'role' => $user->getRoleNames()->first(),
        ], 'Registration successful', 201);
    }

    public function socialLogin(SocialLoginRequest $request): JsonResponse
    {
        $user = $this->authService->socialLogin(
            $request->validated('provider'),
            $request->validated('access_token')
        );

        $token = $this->authService->issueToken($user, $request->validated('provider').'-login');
        event(new ProcessUserData);

        return $this->sendSuccess([
            'token' => $token,
            'permissions' => $user->getPermissionNames(),
            'role' => $user->getRoleNames()->first(),
        ], 'Social login successful');
    }

    public function logout(Request $request): JsonResponse
    {
        $user = $request->user();
        if ($user) {
            $this->authService->logout($user);
        }

        return $this->sendSuccess(true, 'Logged out');
    }
}
