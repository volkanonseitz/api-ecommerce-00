<?php

declare(strict_types=1);

use App\Modules\Tag\Http\Controllers\TagController;
use Illuminate\Support\Facades\Route;

Route::prefix('api/v1')->middleware(['auth:sanctum'])->group(function () {
    // Tags
    Route::apiResource('tags', TagController::class);
});

// Public tags (read-only)
Route::prefix('api/v1')->group(function () {
    Route::get('/public-tags', [TagController::class, 'index']); // Public view
    Route::get('/public-tags/{param}', [TagController::class, 'show']); // Public view
});
