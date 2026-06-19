<?php

namespace App\Providers;

use App\Models\Address;
use App\Policies\AddressPolicy;
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

        Gate::policy(Address::class, AddressPolicy::class);
    }
}
