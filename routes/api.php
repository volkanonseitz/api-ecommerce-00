<?php

use App\Enums\Permission;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{
    AbusiveReportController,
    AddressController,
    AiController,
    AnalyticsController,
    AttachmentController,
    AttributeController,
    AttributeValueController,
    AuthorController,
    BecameSellerController,
    CategoryController,
    CheckoutController,
    ConversationController,
    CouponController,
    DeliveryTimeController,
    DownloadController,
    FaqsController,
    FeedbackController,
    FlashSaleController,
    FlashSaleVendorController,
    LanguageController,
    ManufacturerController,
    MessageController,
    NotifyLogsController,
    OrderController,
    OwnershipTransferController,
    PaymentIntentController,
    PaymentMethodController,
    ProductController,
    QuestionController,
    RefundController,
    RefundPolicyController,
    RefundReasonController,
    ResourceController,
    ReviewController,
    SettingsController,
    ShippingController,
    ShopController,
    StoreNoticeController,
    TagController,
    TaxController,
    TermsAndConditionsController,
    TypeController,
    UserController,
    WebHookController,
    WishlistController,
    WithdrawController,
};

// Broadcast routes
Broadcast::routes(['middleware' => ['auth:sanctum']]);

// ========================
// PUBLIC ROUTES (no auth)
// ========================

// UserController (public)
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
Route::post('/license-key/verify', [UserController::class, 'verifyLicenseKey']);
Route::post('/email/verification-notification', [UserController::class, 'sendVerificationEmail'])
    ->middleware(['auth:sanctum', 'throttle:6,1'])
    ->name('verification.send');

// ProductController (public)
Route::get('/popular-products', [ProductController::class, 'popularProducts']);
Route::get('/best-selling-products', [ProductController::class, 'bestSellingProducts']);
Route::get('/check-availability', [ProductController::class, 'checkAvailability']);
Route::get('/products/calculate-rental-price', [ProductController::class, 'calculateRentalPrice']);
Route::post('/import-products', [ProductController::class, 'importProducts']);
Route::post('/import-variation-options', [ProductController::class, 'importVariationOptions']);
Route::get('/export-products/{shop_id}', [ProductController::class, 'exportProducts']);
Route::get('/export-variation-options/{shop_id}', [ProductController::class, 'exportVariableOptions']);
Route::post('/generate-description', [ProductController::class, 'generateDescription']);
Route::apiResource('/products', ProductController::class)->only(['index', 'show']);

// AuthorController (public)
Route::get('/top-authors', [AuthorController::class, 'topAuthor']);
Route::apiResource('/authors', AuthorController::class)->only(['index', 'show']);

// ManufacturerController (public)
Route::get('/top-manufacturers', [ManufacturerController::class, 'topManufacturer']);
Route::apiResource('/manufacturers', ManufacturerController::class)->only(['index', 'show']);

// TypeController (public)
Route::apiResource('/types', TypeController::class)->only(['index', 'show']);

// AttachmentController (public)
Route::apiResource('/attachments', AttachmentController::class)->only(['index', 'show']);

// CategoryController (public)
Route::apiResource('/categories', CategoryController::class)->only(['index', 'show']);
Route::get('/featured-categories', [CategoryController::class, 'fetchFeaturedCategories']);

// DeliveryTimeController (public)
Route::apiResource('/delivery-times', DeliveryTimeController::class)->only(['index', 'show']);

// LanguageController (public)
Route::apiResource('/languages', LanguageController::class)->only(['index', 'show']);

// TagController (public)
Route::apiResource('/tags', TagController::class)->only(['index', 'show']);

// RefundReasonController (public)
Route::apiResource('/refund-reasons', RefundReasonController::class)->only(['index', 'show']);

// ResourceController (public)
Route::apiResource('/resources', ResourceController::class)->only(['index', 'show']);

// CouponController (public)
Route::apiResource('/coupons', CouponController::class)->only(['index', 'show']);
Route::post('/coupons/verify', [CouponController::class, 'verify']);

// AttributeController (public)
Route::apiResource('/attributes', AttributeController::class)->only(['index', 'show']);
Route::post('/import-attributes', [AttributeController::class, 'importAttributes']);
Route::get('/export-attributes/{shop_id}', [AttributeController::class, 'exportAttributes']);

// ShopController (public)
Route::apiResource('/shops', ShopController::class)->only(['index', 'show']);
Route::get('/near-by-shop/{lat}/{lng}', [ShopController::class, 'nearByShop']);
Route::post('/shop-maintenance-event', [ShopController::class, 'shopMaintenanceEvent']);

// SettingsController (public)
Route::apiResource('/settings', SettingsController::class)->only(['index']);

// ReviewController (public)
Route::apiResource('/reviews', ReviewController::class)->only(['index', 'show']);

// QuestionController (public)
Route::apiResource('/questions', QuestionController::class)->only(['index', 'show']);

// FeedbackController (public)
Route::apiResource('/feedbacks', FeedbackController::class)->only(['index', 'show']);

// CheckoutController (public)
Route::post('/orders/checkout/verify', [CheckoutController::class, 'verify']);

// OrderController (public)
Route::apiResource('/orders', OrderController::class)->only(['show', 'store']);
Route::post('/orders/payment', [OrderController::class, 'submitPayment']);
Route::get('/export-order/token/{token}', [OrderController::class, 'exportOrder'])->name('export_order.token');
Route::get('/download-invoice/token/{token}', [OrderController::class, 'downloadInvoice'])->name('download_invoice.token');

// AiController (public)
Route::post('/generate-descriptions', [AiController::class, 'generateDescription']);

// PaymentIntentController (public)
Route::get('/payment-intent', [PaymentIntentController::class, 'getPaymentIntent']);

// FaqsController (public)
Route::apiResource('/faqs', FaqsController::class)->only(['index', 'show']);

// TermsAndConditionsController (public)
Route::apiResource('/terms-and-conditions', TermsAndConditionsController::class)->only(['index', 'show']);

// FlashSaleController (public)
Route::apiResource('/flash-sale', FlashSaleController::class)->only(['index', 'show']);

// RefundPolicyController (public)
Route::resource('/refund-policies', RefundPolicyController::class)->only(['index', 'show']);

// StoreNoticeController (public)
Route::get('/store-notices', [StoreNoticeController::class, 'index'])->name('store-notices.index');

// DownloadController (public)
Route::get('/download_url/token/{token}', [DownloadController::class, 'downloadFile'])->name('download_url.token');

// WebHookController (public)
Route::prefix('/webhooks')->group(function () {
    Route::post('/razorpay', [WebHookController::class, 'razorpay']);
    Route::post('/stripe', [WebHookController::class, 'stripe']);
    Route::post('/paypal', [WebHookController::class, 'paypal']);
    Route::post('/mollie', [WebHookController::class, 'mollie']);
    Route::post('/paystack', [WebHookController::class, 'paystack']);
    Route::post('/paymongo', [WebHookController::class, 'paymongo']);
    Route::post('/xendit', [WebHookController::class, 'xendit']);
    Route::post('/iyzico', [WebHookController::class, 'iyzico']);
    Route::post('/bkash', [WebHookController::class, 'bkash']);
    Route::post('/flutterwave', [WebHookController::class, 'flutterwave']);
});
Route::get('/callback/flutterwave', [WebHookController::class, 'callback'])->name('callback.flutterwave');

// BecameSellerController (public)
Route::apiResource('/became-seller', BecameSellerController::class);

// ========================
// CUSTOMER (auth:sanctum, email.verified, permission:CUSTOMER)
// ========================
Route::group(['middleware' => ['can:'.Permission::CUSTOMER->value, 'auth:sanctum', 'email.verified']], function () {
    // UserController
    Route::post('/update-email', [UserController::class, 'updateUserEmail']);
    Route::get('/me', [UserController::class, 'me']);
    Route::put('/users/{id}', [UserController::class, 'update']);
    Route::post('/change-password', [UserController::class, 'changePassword']);
    Route::post('/update-contact', [UserController::class, 'updateContact']);

    // OrderController
    Route::apiResource('/orders', OrderController::class)->only(['index']);
    Route::get('/orders/tracking-number/{tracking_number}', [OrderController::class, 'findByTrackingNumber']);

    // ReviewController
    Route::apiResource('/reviews', ReviewController::class)->only(['store', 'update']);

    // QuestionController
    Route::apiResource('/questions', QuestionController::class)->only(['store']);
    Route::get('/my-questions', [QuestionController::class, 'myQuestions']);

    // FeedbackController
    Route::apiResource('/feedbacks', FeedbackController::class)->only(['store']);

    // AbusiveReportController
    Route::apiResource('/abusive_reports', AbusiveReportController::class)->only(['store']);
    Route::get('/my-reports', [AbusiveReportController::class, 'myReports']);

    // ConversationController & MessageController
    Route::apiResource('/conversations', ConversationController::class)->only(['index', 'store']);
    Route::get('/conversations/{conversation_id}', [ConversationController::class, 'show']);
    Route::get('/messages/conversations/{conversation_id}', [MessageController::class, 'index']);
    Route::post('/messages/conversations/{conversation_id}', [MessageController::class, 'store']);
    Route::post('/messages/seen/{conversation_id}', [MessageController::class, 'seen']);

    // WishlistController
    Route::post('/wishlists/toggle', [WishlistController::class, 'toggle']);
    Route::apiResource('/wishlists', WishlistController::class)->only(['index', 'store', 'destroy']);
    Route::get('/wishlists/in_wishlist/{product_id}', [WishlistController::class, 'in_wishlist']);
    Route::get('/my-wishlists', [ProductController::class, 'myWishlists']); // via ProductController

    // AttachmentController
    Route::apiResource('/attachments', AttachmentController::class)->only(['store', 'update', 'destroy']);

    // AddressController
    Route::apiResource('/address', AddressController::class)->only(['destroy']);

    // RefundController
    Route::apiResource('/refunds', RefundController::class)->only(['index', 'store', 'show']);

    // DownloadController
    Route::get('/downloads', [DownloadController::class, 'fetchDownloadableFiles']);
    Route::post('/downloads/digital_file', [DownloadController::class, 'generateDownloadableUrl']);

    // ShopController (follow)
    Route::get('/followed-shops-popular-products', [ShopController::class, 'followedShopsPopularProducts']);
    Route::get('/followed-shops', [ShopController::class, 'userFollowedShops']);
    Route::get('/follow-shop', [ShopController::class, 'userFollowedShop']);
    Route::post('/follow-shop', [ShopController::class, 'handleFollowShop']);

    // PaymentMethodController
    Route::apiResource('/cards', PaymentMethodController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::post('/set-default-card', [PaymentMethodController::class, 'setDefaultCard']);
    Route::post('/save-payment-method', [PaymentMethodController::class, 'savePaymentMethod']);

    // NotifyLogsController
    Route::apiResource('/notify-logs', NotifyLogsController::class)->only(['index', 'show']);
    Route::post('/notify-log-seen', [NotifyLogsController::class, 'readNotifyLogs']);
    Route::post('/notify-log-read-all', [NotifyLogsController::class, 'readAllNotifyLogs']);
});

// ========================
// STAFF & STORE OWNER (permission:STAFF|STORE_OWNER)
// ========================
Route::group(['middleware' => ['permission:'.Permission::STAFF->value.'|'.Permission::STORE_OWNER->value, 'auth:sanctum', 'email.verified']],function () {
    // ProductController
    Route::apiResource('/products', ProductController::class)->only(['store', 'update', 'destroy']);
    Route::get('/draft-products', [ProductController::class, 'draftedProducts']);
    Route::get('/products-stock', [ProductController::class, 'productStock']);
    Route::get('/products-by-flash-sale', [FlashSaleController::class, 'getProductsByFlashSale']);

    // ResourceController
    Route::apiResource('/resources', ResourceController::class)->only(['store']);

    // AttributeController
    Route::apiResource('/attributes', AttributeController::class)->only(['store', 'update', 'destroy']);

    // AttributeValueController
    Route::apiResource('/attribute-values', AttributeValueController::class)->only(['store', 'update', 'destroy']);

    // OrderController
    Route::apiResource('/orders', OrderController::class)->only(['update', 'destroy']);
    Route::get('/export-order-url/{shop_id?}', [OrderController::class, 'exportOrderUrl']);
    Route::post('/download-invoice-url', [OrderController::class, 'downloadInvoiceUrl']);

    // QuestionController
    Route::apiResource('/questions', QuestionController::class)->only(['update']);

    // AuthorController
    Route::apiResource('/authors', AuthorController::class)->only(['store']);

    // ManufacturerController
    Route::apiResource('/manufacturers', ManufacturerController::class)->only(['store']);

    // StoreNoticeController
    Route::get('/store-notices/getStoreNoticeType', [StoreNoticeController::class, 'getStoreNoticeType']);
    Route::get('/store-notices/getUsersToNotify', [StoreNoticeController::class, 'getUsersToNotify']);
    Route::post('/store-notices/read/', [StoreNoticeController::class, 'readNotice']);
    Route::post('/store-notices/read-all', [StoreNoticeController::class, 'readAllNotice']);
    Route::apiResource('/store-notices', StoreNoticeController::class)->only(['show', 'store', 'update', 'destroy']);

    // FaqsController
    Route::apiResource('/faqs', FaqsController::class)->only(['store', 'update', 'destroy']);

    // AnalyticsController
    Route::get('/analytics', [AnalyticsController::class, 'analytics']);
    Route::get('/low-stock-products', [AnalyticsController::class, 'lowStockProducts']);
    Route::get('/category-wise-product', [AnalyticsController::class, 'categoryWiseProduct']);
    Route::get('/category-wise-product-sale', [AnalyticsController::class, 'categoryWiseProductSale']);
    Route::get('/top-rate-product', [AnalyticsController::class, 'topRatedProducts']);

    // CouponController
    Route::apiResource('/coupons', CouponController::class)->only(['update']);

    // FlashSaleVendorController
    Route::get('/requested-products-for-flash-sale', [FlashSaleVendorController::class, 'getRequestedProductsForFlashSale']);
    Route::apiResource('/vendor-requests-for-flash-sale', FlashSaleVendorController::class)->only(['index', 'show', 'store', 'destroy']);
});

// ========================
// STORE OWNER ONLY (permission:STORE_OWNER)
// ========================
Route::group(['middleware' => ['permission:'.Permission::STORE_OWNER->value, 'auth:sanctum', 'email.verified']], function () {
    // ShopController
    Route::apiResource('/shops', ShopController::class)->only(['store', 'update', 'destroy']);
    Route::get('/my-shops', [ShopController::class, 'myShops']);
    Route::post('/transfer-shop-ownership', [ShopController::class, 'transferShopOwnership']);
    Route::post('/staffs', [ShopController::class, 'addStaff']);
    Route::delete('/staffs/{id}', [ShopController::class, 'deleteStaff']);
    Route::get('/staffs', [UserController::class, 'staffs']);

    // WithdrawController
    Route::apiResource('/withdraws', WithdrawController::class)->only(['store', 'index', 'show']);

    // FlashSaleController
    Route::apiResource('/flash-sale', FlashSaleController::class)->only(['store', 'update', 'destroy']);
    Route::get('/product-flash-sale-info', [FlashSaleController::class, 'getFlashSaleInfoByProductID']);

    // TermsAndConditionsController
    Route::apiResource('/terms-and-conditions', TermsAndConditionsController::class)->only(['store', 'update', 'destroy']);

    // CouponController
    Route::apiResource('/coupons', CouponController::class)->only(['store', 'destroy']);

    // UserController (vendor list)
    Route::get('/vendors/list', [UserController::class, 'vendors']);

    // OwnershipTransferController
    Route::apiResource('/ownership-transfer', OwnershipTransferController::class)->only(['index', 'show']);
});

// ========================
// SUPER ADMIN ONLY (permission:SUPER_ADMIN)
// ========================
Route::group(['middleware' => ['permission:'.Permission::SUPER_ADMIN->value, 'auth:sanctum', 'email.verified']], function () {
    // TypeController
    Route::apiResource('/types', TypeController::class)->only(['store', 'update', 'destroy']);

    // WithdrawController
    Route::apiResource('/withdraws', WithdrawController::class)->only(['update', 'destroy']);
    Route::post('/approve-withdraw', [WithdrawController::class, 'approveWithdraw']);

    // CategoryController
    Route::apiResource('/categories', CategoryController::class)->only(['store', 'update', 'destroy']);

    // DeliveryTimeController
    Route::apiResource('/delivery-times', DeliveryTimeController::class)->only(['store', 'update', 'destroy']);

    // LanguageController
    Route::apiResource('/languages', LanguageController::class)->only(['store', 'update', 'destroy']);

    // TagController
    Route::apiResource('/tags', TagController::class)->only(['store', 'update', 'destroy']);

    // RefundReasonController
    Route::apiResource('/refund-reasons', RefundReasonController::class)->only(['store', 'update', 'destroy']);

    // ResourceController
    Route::apiResource('/resources', ResourceController::class)->only(['update', 'destroy']);

    // ReviewController
    Route::apiResource('/reviews', ReviewController::class)->only(['destroy']);

    // QuestionController
    Route::apiResource('/questions', QuestionController::class)->only(['destroy']);

    // FeedbackController
    Route::apiResource('/feedbacks', FeedbackController::class)->only(['update', 'destroy']);

    // AbusiveReportController
    Route::apiResource('/abusive_reports', AbusiveReportController::class)->only(['index', 'show', 'update', 'destroy']);
    Route::post('/abusive_reports/accept', [AbusiveReportController::class, 'accept']);
    Route::post('/abusive_reports/reject', [AbusiveReportController::class, 'reject']);

    // SettingsController
    Route::apiResource('/settings', SettingsController::class)->only(['store']);

    // UserController
    Route::apiResource('/users', UserController::class);
    Route::post('/users/block-user', [UserController::class, 'banUser']);
    Route::post('/users/unblock-user', [UserController::class, 'activeUser']);
    Route::post('/add-points', [UserController::class, 'addPoints']);
    Route::post('/users/make-admin', [UserController::class, 'makeOrRevokeAdmin']);
    Route::get('/admin/list', [UserController::class, 'admins']);
    Route::get('/customers/list', [UserController::class, 'customers']);
    Route::get('/my-staffs', [UserController::class, 'myStaffs']);
    Route::get('/all-staffs', [UserController::class, 'allStaffs']);

    // AuthorController
    Route::apiResource('/authors', AuthorController::class)->only(['update', 'destroy']);

    // ManufacturerController
    Route::apiResource('/manufacturers', ManufacturerController::class)->only(['update', 'destroy']);

    // TaxController
    Route::apiResource('/taxes', TaxController::class);

    // ShippingController
    Route::apiResource('/shippings', ShippingController::class);

    // ShopController (approve/disapprove, new shops)
    Route::post('/approve-shop', [ShopController::class, 'approveShop']);
    Route::post('/disapprove-shop', [ShopController::class, 'disApproveShop']);
    Route::get('/new-shops', [ShopController::class, 'newOrInActiveShops']);

    // RefundController
    Route::apiResource('/refunds', RefundController::class)->only(['destroy', 'update']);

    // NotifyLogsController
    Route::apiResource('/notify-logs', NotifyLogsController::class)->only(['destroy']);

    // TermsAndConditionsController (approve/disapprove)
    Route::post('/approve-terms-and-conditions', [TermsAndConditionsController::class, 'approveTerm']);
    Route::post('/disapprove-terms-and-conditions', [TermsAndConditionsController::class, 'disApproveTerm']);

    // RefundPolicyController
    Route::resource('/refund-policies', RefundPolicyController::class)->only(['store', 'update', 'destroy']);

    // CouponController (approve/disapprove)
    Route::post('/approve-coupon', [CouponController::class, 'approveCoupon']);
    Route::post('/disapprove-coupon', [CouponController::class, 'disApproveCoupon']);

    // FlashSaleVendorController
    Route::post('/approve-flash-sale-requested-products', [FlashSaleVendorController::class, 'approveFlashSaleProductsRequest']);
    Route::post('/disapprove-flash-sale-requested-products', [FlashSaleVendorController::class, 'disapproveFlashSaleProductsRequest']);
    Route::apiResource('/vendor-requests-for-flash-sale', FlashSaleVendorController::class)->only(['update']);

    // OwnershipTransferController
    Route::apiResource('/ownership-transfer', OwnershipTransferController::class)->only(['update', 'destroy']);
});