<?php

declare(strict_types=1);

namespace App\Modules\Order;

use App\Models\Order;
use App\Modules\Order\Actions\CreateOrderAction;
use App\Modules\Order\Actions\UpdateOrderStatusAction;
use App\Modules\Order\Policies\OrderPolicy;
use App\Modules\Order\Services\OrderCacheService;
use App\Modules\Order\Services\OrderInventoryService;
use App\Modules\Order\Services\OrderQueryService;
use App\Modules\Order\Services\OrderTransactionService;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class OrderModuleServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Register Actions
        $this->app->singleton(CreateOrderAction::class);
        $this->app->singleton(UpdateOrderStatusAction::class);

        // Register Services
        $this->app->singleton(OrderQueryService::class);
        $this->app->singleton(OrderCacheService::class);
        $this->app->singleton(OrderTransactionService::class);
        $this->app->singleton(OrderInventoryService::class);
    }

    public function boot(): void
    {
        $this->registerPolicies();
    }

    protected function registerPolicies(): void
    {
        Gate::policy(Order::class, OrderPolicy::class);
    }
}
