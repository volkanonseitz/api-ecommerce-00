<?php

declare(strict_types=1);

use App\Modules\Refund\Http\Controllers\RefundController;
use App\Modules\Refund\Http\Controllers\RefundPolicyController;
use Illuminate\Support\Facades\Route;

Route::prefix('api/v1')->middleware(['auth:sanctum'])->group(function () {
    // Refund Policies (Admin/Owner only)
    Route::apiResource('refund-policies', RefundPolicyController::class);

    // Refunds (Admin/Owner/Customer)
    Route::apiResource('refunds', RefundController::class);
});
