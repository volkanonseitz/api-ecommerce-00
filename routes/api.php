<?php

use App\Enums\Permission;
use App\Http\Controllers\AuthorController;
use App\Http\Controllers\ManufacturerController;
use App\Http\Controllers\TypeController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Route;

/**
 * ******************************************
 * Available Public Routes
 * ******************************************
 */
Broadcast::routes(['middleware' => ['auth:sanctum']]);

Route::get('/email/verify/{id}/{hash}', [UserController::class, 'verifyEmail'])->name('verification.verify');

Route::post('/register', [UserController::class, 'register']);
Route::post('/token', [UserController::class, 'token']);
Route::post('/logout', [UserController::class, 'logout']);
Route::post('/forget-password', [UserController::class, 'forgetPassword']);
Route::post('/verify-forget-password-token', [UserController::class, 'verifyForgetPasswordToken']);
Route::post('/reset-password', [UserController::class, 'resetPassword']);
Route::post('/contact-us', [UserController::class, 'contactAdmin']);
Route::post('/social-login-token', [UserController::class, 'socialLogin']);
Route::post('/send-otp-code', [UserController::class, 'sendOtpCode']);
Route::post('/verify-otp-code', [UserController::class, 'verifyOtpCode']);
Route::post('/otp-login', [UserController::class, 'otpLogin']);
Route::post('/subscribe-to-newsletter', [UserController::class, 'subscribeToNewsletter'])->name('subscribeToNewsletter');

Route::post('/email/verification-notification', [UserController::class, 'sendVerificationEmail'])
    ->middleware(['auth:sanctum', 'throttle:6,1'])
    ->name('verification.send');

Route::get('top-authors', [AuthorController::class, 'topAuthor']);
Route::apiResource('authors', AuthorController::class, [
    'only' => ['index', 'show'],
]);

Route::get('top-manufacturers', [ManufacturerController::class, 'topManufacturer']);
Route::apiResource('manufacturers', ManufacturerController::class, [
    'only' => ['index', 'show'],
]);

Route::apiResource('types', TypeController::class, [
    'only' => ['index', 'show'],
]);

Route::get('popular-products', [ProductController::class, 'popularProducts']);
Route::get('best-selling-products', [ProductController::class, 'bestSellingProducts']);
Route::get('check-availability', [ProductController::class, 'checkAvailability']);
Route::get('products/calculate-rental-price', [ProductController::class, 'calculateRentalPrice']);
Route::post('import-products', [ProductController::class, 'importProducts']);
Route::post('import-variation-options', [ProductController::class, 'importVariationOptions']);
Route::get('export-products/{shop_id}', [ProductController::class, 'exportProducts']);
Route::get('export-variation-options/{shop_id}', [ProductController::class, 'exportVariableOptions']);
Route::post('generate-description', [ProductController::class, 'generateDescription']);
Route::apiResource('products', ProductController::class, [
    'only' => ['index', 'show'],
]);

/**
 * ******************************************
 * Authorized Route for Customers only
 * ******************************************
 */
Route::group(['middleware' => ['can:'.Permission::CUSTOMER->value, 'auth:sanctum', 'email.verified']], function () {
    Route::post('/update-email', [UserController::class, 'updateUserEmail']);
    Route::get('me', [UserController::class, 'me']);
    Route::put('users/{id}', [UserController::class, 'update']);
    Route::post('/change-password', [UserController::class, 'changePassword']);
    Route::post('/update-contact', [UserController::class, 'updateContact']);

    Route::get('my-wishlists', [ProductController::class, 'myWishlists']);
});

/**
 * ******************************************
 * Authorized Route for Staff & Store Owner
 * ******************************************
 */
Route::group(
    ['middleware' => ['permission:'.Permission::STAFF->value.'|'.Permission::STORE_OWNER->value, 'auth:sanctum', 'email.verified']],
    function () {
        Route::apiResource('authors', AuthorController::class, [
            'only' => ['store'],
        ]);

        Route::apiResource('manufacturers', ManufacturerController::class, [
            'only' => ['store'],
        ]);

        Route::apiResource('products', ProductController::class, [
            'only' => ['store', 'update', 'destroy'],
        ]);
        Route::get('draft-products', [ProductController::class, 'draftedProducts']);
        Route::get('products-stock', [ProductController::class, 'productStock']);
    }
);
/**
 * *****************************************
 * Authorized Route for Store owner Only
 * *****************************************
 */
Route::group(
    ['middleware' => ['permission:'.Permission::STORE_OWNER->value, 'auth:sanctum', 'email.verified']],
    function () {
        Route::get('staffs', [UserController::class, 'staffs']);
        // Route::get('/admin/list', [UserController::class, 'admins']);
        Route::get('/vendors/list', [UserController::class, 'vendors']);
    }
);

/**
 * *****************************************
 * Authorized Route for Super Admin only
 * *****************************************
 */
Route::group(['middleware' => ['permission:'.Permission::SUPER_ADMIN->value, 'auth:sanctum', 'email.verified']], function () {
    Route::apiResource('users', UserController::class);
    Route::post('users/block-user', [UserController::class, 'banUser']);
    Route::post('users/unblock-user', [UserController::class, 'activeUser']);
    Route::post('add-points', [UserController::class, 'addPoints']);
    Route::post('users/make-admin', [UserController::class, 'makeOrRevokeAdmin']);
    Route::get('/admin/list', [UserController::class, 'admins']);

    Route::get('/customers/list', [UserController::class, 'customers']);
    Route::get('my-staffs', [UserController::class, 'myStaffs']);
    Route::get('all-staffs', [UserController::class, 'allStaffs']);

    Route::apiResource('authors', AuthorController::class, [
        'only' => ['update', 'destroy'],
    ]);

    Route::apiResource('manufacturers', ManufacturerController::class, [
        'only' => ['update', 'destroy'],
    ]);

    Route::apiResource('types', TypeController::class, [
        'only' => ['store', 'update', 'destroy'],
    ]);

});
