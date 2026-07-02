<?php

declare(strict_types=1);

use App\Modules\User\Http\Controllers\AuthController;
use App\Modules\User\Http\Controllers\ProfileController;
use App\Modules\User\Http\Controllers\UserManagementController;
use App\Modules\User\Http\Controllers\UserSecurityController;
use Illuminate\Support\Facades\Route;

Route::prefix('api')->group(function () {
    // Public Auth Routes
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/social-login', [AuthController::class, 'socialLogin']);
    Route::post('/password/forgot', [AuthController::class, 'forgotPassword']);
    Route::post('/password/reset', [AuthController::class, 'resetPassword']);

    // Authenticated User Routes
    Route::middleware(['auth:sanctum'])->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me', [ProfileController::class, 'me']);
        Route::put('/me', [ProfileController::class, 'updateProfile']);
        Route::post('/me/avatar', [ProfileController::class, 'updateAvatar']);
        Route::delete('/me/avatar', [ProfileController::class, 'deleteAvatar']);

        // Security routes
        Route::post('/me/change-password', [UserSecurityController::class, 'changePassword']);
        Route::post('/me/logout-all', [UserSecurityController::class, 'logoutFromAllDevices']);
        Route::get('/me/sessions', [UserSecurityController::class, 'viewActiveSessions']);
        Route::delete('/me/sessions/{sessionId}', [UserSecurityController::class, 'revokeSession']);

        // Admin/Super Admin User Management
        Route::middleware(['permission:super_admin'])->group(function () {
            Route::apiResource('/users', UserManagementController::class);
            Route::patch('/users/{id}/toggle-active', [UserManagementController::class, 'toggleActive']);
            Route::patch('/users/{id}/toggle-admin', [UserManagementController::class, 'toggleAdmin']);
            Route::patch('/users/{id}/assign-shop', [UserManagementController::class, 'assignShop']);
        });
    });
});
