<?php

declare(strict_types=1);

use App\Modules\Review\Http\Controllers\ReviewController;
use Illuminate\Support\Facades\Route;

Route::prefix('api')->middleware(['auth:sanctum'])->group(function () {
    // Reviews
    Route::apiResource('reviews', ReviewController::class);
});

// Public reviews (read-only)
Route::prefix('api')->group(function () {
    Route::get('/products/{productId}/reviews', [ReviewController::class, 'index']); // Get reviews for a product
});
