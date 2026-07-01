<?php

declare(strict_types=1);

namespace App\Modules\Payment\Providers;

use App\Modules\Payment\Actions\CreatePaymentIntentAction;
use App\Modules\Payment\Actions\DeletePaymentMethodAction;
use App\Modules\Payment\Actions\GetPaymentMethodsAction;
use App\Modules\Payment\Actions\InitializePaymentMethodAction;
use App\Modules\Payment\Actions\SetDefaultPaymentMethodAction;
use App\Modules\Payment\Actions\StorePaymentMethodAction;
use App\Modules\Payment\Contracts\PaymentGatewayFactoryInterface;
use App\Modules\Payment\Factory\PaymentGatewayFactory;
use App\Modules\Payment\Policies\PaymentMethodPolicy;
use App\Modules\Payment\Services\PaymentMethodPersistService;
use App\Modules\Payment\Services\PaymentMethodQueryService;
use Illuminate\Support\ServiceProvider;

final class PaymentServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Bind interfaces to implementations
        $this->app->singleton(PaymentGatewayFactoryInterface::class, PaymentGatewayFactory::class);
        
        // Register Actions
        $this->app->singleton(GetPaymentMethodsAction::class);
        $this->app->singleton(StorePaymentMethodAction::class);
        $this->app->singleton(DeletePaymentMethodAction::class);
        $this->app->singleton(SetDefaultPaymentMethodAction::class);
        $this->app->singleton(InitializePaymentMethodAction::class);
        $this->app->singleton(CreatePaymentIntentAction::class);
        
        // Register Services
        $this->app->singleton(PaymentMethodQueryService::class);
        $this->app->singleton(PaymentMethodPersistService::class);
        
        // Register Gateway Providers
        $this->app->singleton(\App\Modules\Payment\Providers\StripeProvider::class);
        $this->app->singleton(\App\Services\Payment\Providers\MidtransProvider::class);
        $this->app->singleton(\App\Services\Payment\Providers\XenditProvider::class);
    }

    public function boot(): void
    {
        // Register policies
        $this->gate();
        
        // Publish configuration if needed
        $this->publishes([
            __DIR__ . '/../config/payment.php' => config_path('payment.php'),
        ], 'payment-config');
    }

    private function gate(): void
    {
        \Gate::policy(\App\Models\PaymentMethod::class, PaymentMethodPolicy::class);
        \Gate::policy(\App\Models\PaymentIntent::class, \App\Modules\PaymentIntent\Policies\PaymentIntentPolicy::class);
    }
}