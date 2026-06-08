<?php

use App\Enums\Permission;
use App\Http\Controllers\AuthorController;
use App\Http\Controllers\ManufacturerController;
use App\Http\Controllers\TypeController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\AttributeController;
use App\Http\Controllers\AttributeValueController;
use App\Http\Controllers\ShopController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\DownloadController;
use App\Http\Controllers\StoreNoticeController;
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

Route::post('import-attributes', [AttributeController::class, 'importAttributes']);
Route::get('export-attributes/{shop_id}', [AttributeController::class, 'exportAttributes']);
Route::apiResource('attributes', AttributeController::class, [
    'only' => ['index', 'show'],
]);

Route::get('near-by-shop/{lat}/{lng}', [ShopController::class, 'nearByShop']);
Route::apiResource('shops', ShopController::class, [
    'only' => ['index', 'show'],
]);
Route::post('shop-maintenance-event', [ShopController::class, 'shopMaintenanceEvent']);

Route::get('export-order/token/{token}', [OrderController::class, 'exportOrder'])->name('export_order.token');
Route::get('download-invoice/token/{token}', [OrderController::class, 'downloadInvoice'])->name('download_invoice.token');
Route::apiResource('orders', OrderController::class, [
    'only' => ['show', 'store'],
]);
Route::post('orders/payment', [OrderController::class, 'submitPayment']);

Route::apiResource('categories', CategoryController::class, [
    'only' => ['index', 'show'],
]);
Route::get('featured-categories', [CategoryController::class, 'featuredCategories']);

Route::get('/download/token/{token}', [DownloadController::class, 'downloadFile'])->name('download_url.token');

Route::get('store-notices', [StoreNoticeController::class, 'index'])->name('store-notices.index');

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

    Route::get('/followed-shops-popular-products', [ShopController::class, 'followedShopsPopularProducts']);
    Route::get('/followed-shops', [ShopController::class, 'userFollowedShops']);
    Route::get('/follow-shop', [ShopController::class, 'userFollowedShop']);
    Route::post('/follow-shop', [ShopController::class, 'handleFollowShop']);

    Route::apiResource('orders', OrderController::class, [
        'only' => ['index'],
    ]);
    Route::get('orders/tracking-number/{tracking_number}', [OrderController::class, 'findByTrackingNumber']);

    Route::get('downloads', [DownloadController::class, 'fetchDownloadableFiles']);
    Route::post('downloads/digital_file', [DownloadController::class, 'generateDownloadableUrl']);
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

        Route::apiResource('orders', OrderController::class, [
            'only' => ['update', 'destroy'],
        ]);
        Route::get('export-order-url/{shop_id?}', [OrderController::class, 'exportOrderUrl']);
        Route::post('download-invoice-url', [OrderController::class, 'downloadInvoiceUrl']);

        Route::get('store-notices/getStoreNoticeType', [StoreNoticeController::class, 'getStoreNoticeType']);
        Route::get('store-notices/getUsersToNotify', [StoreNoticeController::class, 'getUsersToNotify']);
        Route::post('store-notices/read/', [StoreNoticeController::class, 'readNotice']);
        Route::post('store-notices/read-all', [StoreNoticeController::class, 'readAllNotice']);
        Route::apiResource('store-notices', StoreNoticeController::class, [
            'only' => ['show', 'store', 'update', 'destroy']
        ]);
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

        Route::apiResource('attributes', AttributeController::class, [
            'only' => ['store', 'update', 'destroy'],
        ]);
        Route::apiResource('attribute-values', AttributeValueController::class, [
            'only' => ['store', 'update', 'destroy'],
        ]);

        Route::apiResource('shops', ShopController::class, [
            'only' => ['store', 'update', 'destroy'],
        ]);
        Route::post('staffs', [ShopController::class, 'addStaff']);
        Route::delete('staffs/{id}', [ShopController::class, 'deleteStaff']);
        Route::get('my-shops', [ShopController::class, 'myShops']);
        Route::post('transfer-shop-ownership', [ShopController::class, 'transferShopOwnership']);
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

    Route::post('approve-shop', [ShopController::class, 'approveShop']);
    Route::post('disapprove-shop', [ShopController::class, 'disApproveShop']);
    Route::get('new-shops', [ShopController::class, 'newOrInActiveShops']);

    Route::apiResource('categories', CategoryController::class, [
        'only' => ['store', 'update', 'destroy'],
    ]);

});
