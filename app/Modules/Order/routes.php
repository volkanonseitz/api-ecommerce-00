<?php

declare(strict_types=1);

use App\Modules\Order\Http\Controllers\OrderQueryController;
use App\Modules\Order\Http\Controllers\OrderTransactionController;
use Illuminate\Support\Facades\Route;

// Public Routes (tanpa authentication)
Route::prefix('api')->group(function () {
    // Track order (guest access)
    Route::get('/orders/track/{identifier}', [OrderQueryController::class, 'show']);
});

// Authenticated Routes
Route::prefix('api')->middleware(['auth:sanctum'])->group(function () {
    // Customer orders
    Route::get('/my-orders', [OrderQueryController::class, 'myOrders']);
    Route::post('/orders', [OrderTransactionController::class, 'store']);
    Route::get('/orders/{identifier}', [OrderQueryController::class, 'show']);
    Route::post('/orders/{id}/cancel', [OrderTransactionController::class, 'cancel']);

    // Admin/Staff Order Management
    Route::middleware(['permission:super_admin|store_owner|staff'])->group(function () {
        Route::get('/orders', [OrderQueryController::class, 'index']);
        Route::patch('/orders/{id}/status', [OrderTransactionController::class, 'updateStatus']);
        Route::patch('/orders/{id}/payment-status', [OrderTransactionController::class, 'updatePaymentStatus']);

        // Shop-specific orders
        Route::get('/shops/{shopId}/orders', [OrderQueryController::class, 'showByShop']);

        // Order statistics
        Route::get('/orders/stats', [OrderQueryController::class, 'stats']);
    });
});
