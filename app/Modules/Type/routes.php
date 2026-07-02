<?php

declare(strict_types=1);

use App\Modules\Type\Http\Controllers\TypeController;
use Illuminate\Support\Facades\Route;

Route::prefix('api/types')->group(function () {
    Route::get('/', [TypeController::class, 'index']);
    Route::post('/', [TypeController::class, 'store']);
    Route::get('/{identifier}', [TypeController::class, 'show']);
    Route::put('/{id}', [TypeController::class, 'update']);
    Route::delete('/{id}', [TypeController::class, 'destroy']);
});
