<?php

namespace App\Providers;

use App\Enums\Permission;
use App\Models\AbusiveReport;
use App\Models\Address;
use App\Models\Attachment;
use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\Author;
use App\Models\BecameSeller;
use App\Models\Category;
use App\Models\Conversation;
use App\Models\Coupon;
use App\Models\Faqs;
use App\Models\Feedback;
use App\Models\FlashSale;
use App\Models\FlashSaleRequest;
use App\Models\Language;
use App\Models\Manufacturer;
use App\Models\Message;
use App\Models\NotifyLogs;
use App\Models\Order;
use App\Models\OrderedFile;
use App\Models\PaymentIntent;
use App\Models\Product;
use App\Models\User;
use App\Modules\AbusiveReport\Policies\AbusiveReportPolicy;
use App\Modules\Address\Policies\AddressPolicy;
use App\Modules\Address\Services\AddressFormatterService;
use App\Modules\Attachment\Policies\AttachmentPolicy;
use App\Modules\Attribute\Policies\AttributePolicy;
use App\Modules\Attribute\Policies\AttributeValuePolicy;
use App\Modules\Author\Policies\AuthorPolicy;
use App\Modules\BecameSeller\Policies\BecameSellerPolicy;
use App\Modules\Category\Policies\CategoryPolicy;
use App\Modules\Checkout\Policies\CheckoutPolicy;
use App\Modules\Conversation\Policies\ConversationPolicy;
use App\Modules\Coupon\Policies\CouponPolicy;
use App\Modules\Download\Policies\DownloadPolicy;
use App\Modules\Faqs\Policies\FaqsPolicy;
use App\Modules\Feedback\Policies\FeedbackPolicy;
use App\Modules\FlashSale\Policies\FlashSalePolicy;
use App\Modules\FlashSaleRequest\Policies\FlashSaleRequestPolicy;
use App\Modules\Language\Policies\LanguagePolicy;
use App\Modules\Manufacturer\Policies\ManufacturerPolicy;
use App\Modules\Message\Policies\MessagePolicy;
use App\Modules\NotifyLogs\Policies\NotifyLogsPolicy;
use App\Modules\Order\Policies\OrderPolicy;
use App\Modules\PaymentIntent\Policies\PaymentIntentPolicy;
use App\Modules\Product\Policies\ProductPolicy;
use App\Services\CurrencyFormatterService;
use App\Services\Otp\OtpService;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(OtpService::class, function ($app) {
            return new OtpService;
        });

        $this->app->bind(AddressFormatterService::class, function ($app) {
            return new AddressFormatterService;
        });
        $this->app->bind(CurrencyFormatterService::class, function ($app) {
            return new CurrencyFormatterService;
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        RateLimiter::for('login', function (Request $request) {
            return Limit::perMinute(5)->by(
                $request->ip().'|'.$request->input('email')
            );
        });

        RateLimiter::for('otp', function (Request $request) {
            return Limit::perMinute(3)->by(
                $request->ip().'|'.$request->input('phone_number')
            );
        });

        RateLimiter::for('password-reset', function (Request $request) {
            return Limit::perMinute(3)->by(
                $request->ip().'|'.$request->input('email')
            );
        });

        // Registrasi Policy
        Gate::policy(Address::class, AddressPolicy::class);
        Gate::policy(AbusiveReport::class, AbusiveReportPolicy::class);
        Gate::policy(Order::class, OrderPolicy::class);
        Gate::policy(Product::class, ProductPolicy::class);
        Gate::policy(Attachment::class, AttachmentPolicy::class);
        Gate::policy(Attribute::class, AttributePolicy::class);
        Gate::policy(AttributeValue::class, AttributeValuePolicy::class);
        Gate::policy(Category::class, CategoryPolicy::class);
        Gate::policy(BecameSeller::class, BecameSellerPolicy::class);
        Gate::policy(Author::class, AuthorPolicy::class);
        Gate::policy(User::class, CheckoutPolicy::class);
        Gate::policy(Conversation::class, ConversationPolicy::class);
        Gate::policy(Coupon::class, CouponPolicy::class);
        Gate::policy(OrderedFile::class, DownloadPolicy::class);
        Gate::policy(Faqs::class, FaqsPolicy::class);
        Gate::policy(Feedback::class, FeedbackPolicy::class);
        Gate::policy(FlashSale::class, FlashSalePolicy::class);
        Gate::policy(FlashSaleRequest::class, FlashSaleRequestPolicy::class);
        Gate::policy(Language::class, LanguagePolicy::class);
        Gate::policy(Manufacturer::class, ManufacturerPolicy::class);
        Gate::policy(Message::class, MessagePolicy::class);
        Gate::policy(NotifyLogs::class, NotifyLogsPolicy::class);
        Gate::policy(PaymentIntent::class, PaymentIntentPolicy::class); // sedang tidak digunakan

        Gate::define('view-analytics', function ($user) {
            return $user->hasPermissionTo(Permission::SUPER_ADMIN->value) ||
                   $user->hasPermissionTo(Permission::STORE_OWNER->value) ||
                   $user->hasPermissionTo(Permission::STAFF->value);
        });

        Gate::define('verify-checkout', function ($user) {
            return $user !== null;
        });
    }
}
