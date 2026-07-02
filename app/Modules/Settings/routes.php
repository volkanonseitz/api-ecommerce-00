<?php

declare(strict_types=1);

use App\Modules\Settings\Http\Controllers\SettingsController;
use Illuminate\Support\Facades\Route;

Route::prefix('api/v1')->middleware(['auth:sanctum', 'permission:super_admin'])->group(function () {
    // Settings (Super Admin only)
    Route::get('/settings', [SettingsController::class, 'index']);
    Route::post('/settings', [SettingsController::class, 'store']); // Use store to create/update
    Route::put('/settings/{id}', [SettingsController::class, 'update']);
    Route::get('/settings/{id}', [SettingsController::class, 'show']);
});

// Publicly accessible settings (read-only for general info)
Route::prefix('api/v1')->group(function () {
    Route::get('/public-settings', [SettingsController::class, 'index']); // Public view, maybe filtered
});
