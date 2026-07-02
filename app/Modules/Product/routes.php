<?php

declare(strict_types=1);

use App\Modules\Product\Http\Controllers\ProductCrudController;
use App\Modules\Product\Http\Controllers\ProductMetricController;
use App\Modules\Product\Http\Controllers\ProductQueryController;
use App\Modules\Product\Http\Controllers\ProductRentalController;
use Illuminate\Support\Facades\Route;

// Public Routes (tanpa authentication)
Route::prefix('api/v1')->group(function () {
    // Public product queries
    Route::get('/popular-products', [ProductMetricController::class, 'popular']);
    Route::get('/best-selling-products', [ProductMetricController::class, 'bestSelling']);
    Route::get('/check-availability', [ProductRentalController::class, 'checkAvailability']);
    Route::get('/products/calculate-rental-price', [ProductRentalController::class, 'calculateRentalPrice']);

    // Public product queries
    Route::apiResource('/products', ProductQueryController::class)->only(['index', 'show']);
});

// Authenticated Routes
Route::prefix('api/v1')->middleware(['auth:sanctum'])->group(function () {
    // Wishlist
    Route::get('/my-wishlists', [ProductQueryController::class, 'myWishlists']);

    // Shop-specific products
    Route::get('/followed-shops-popular-products', [ProductQueryController::class, 'followedShopsPopularProducts']);

    // Admin/Staff Product Management
    Route::middleware(['permission:super_admin|store_owner|staff'])->group(function () {
        Route::apiResource('/products', ProductCrudController::class)->only(['store', 'update', 'destroy']);
        Route::patch('/products/{id}/stock', [ProductCrudController::class, 'updateStock']);
        Route::patch('/products/{id}/status', [ProductCrudController::class, 'changeStatus']);

        Route::get('/draft-products', [ProductQueryController::class, 'draftedProducts']);
        Route::get('/products-stock', [ProductQueryController::class, 'productStock']);
        Route::get('/products-by-flash-sale', [ProductQueryController::class, 'getProductsByFlashSale']);

        // Rental Management
        Route::post('/rental/availability', [ProductRentalController::class, 'checkAvailability']);
        Route::get('/rental/blocked-dates/{productId}', [ProductRentalController::class, 'getBlockedDates']);
    });

    // Import/Export
    Route::middleware(['permission:super_admin|store_owner'])->group(function () {
        Route::post('/import-products', [ProductCrudController::class, 'importProducts']);
        Route::post('/import-variation-options', [ProductCrudController::class, 'importVariationOptions']);
        Route::get('/export-products/{shop_id}', [ProductCrudController::class, 'exportProducts']);
        Route::get('/export-variation-options/{shop_id}', [ProductCrudController::class, 'exportVariableOptions']);
        Route::post('/generate-description', [ProductCrudController::class, 'generateDescription']);
    });

    // Analytics Routes (Admin only)
    Route::middleware(['permission:super_admin'])->prefix('admin')->group(function () {
        Route::get('/low-stock-products', [ProductMetricController::class, 'lowStock']);
        Route::get('/category-wise-product', [ProductMetricController::class, 'categoryWiseProduct']);
        Route::get('/category-wise-product-sale', [ProductMetricController::class, 'categoryWiseProductSale']);
        Route::get('/top-rate-product', [ProductMetricController::class, 'topRatedProducts']);

        // Flash Sale Products
        Route::get('/requested-products-for-flash-sale', [ProductMetricController::class, 'getRequestedProductsForFlashSale']);
        Route::post('/approve-flash-sale-requested-products', [ProductMetricController::class, 'approveFlashSaleProductsRequest']);
        Route::post('/disapprove-flash-sale-requested-products', [ProductMetricController::class, 'disapproveFlashSaleProductsRequest']);
        Route::get('/product-flash-sale-info', [ProductMetricController::class, 'getFlashSaleInfoByProductID']);
    });
});
