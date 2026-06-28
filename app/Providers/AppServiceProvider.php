<?php

namespace App\Providers;

use App\Enums\Permission;
use App\Models\AbusiveReport;
use App\Models\Address;
use App\Models\Attachment;
use App\Models\Order;
use App\Models\Product;
use App\Modules\AbusiveReport\Policies\AbusiveReportPolicy;
use App\Modules\Address\Policies\AddressPolicy;
use App\Modules\Address\Services\AddressFormatterService;
use App\Modules\Attachment\Policies\AttachmentPolicy;
use App\Modules\Order\Policies\OrderPolicy;
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

        Gate::define('view-analytics', function ($user) {
            return $user->hasPermissionTo(Permission::SUPER_ADMIN->value) ||
                   $user->hasPermissionTo(Permission::STORE_OWNER->value) ||
                   $user->hasPermissionTo(Permission::STAFF->value);
        });
    }
}
