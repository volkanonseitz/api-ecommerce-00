<?php

declare(strict_types=1);

namespace App\Modules\Product;

use App\Models\Product;
use App\Modules\Product\Actions\CreateProductAction;
use App\Modules\Product\Actions\DeleteProductAction;
use App\Modules\Product\Actions\UpdateProductAction;
use App\Modules\Product\Policies\ProductPolicy;
use App\Modules\Product\Services\ProductCacheService;
use App\Modules\Product\Services\ProductCrudService;
use App\Modules\Product\Services\ProductMetricService;
use App\Modules\Product\Services\ProductQueryService;
use App\Modules\Product\Services\ProductRentalService;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class ProductModuleServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Register Actions
        $this->app->singleton(CreateProductAction::class);
        $this->app->singleton(UpdateProductAction::class);
        $this->app->singleton(DeleteProductAction::class);

        // Register Services
        $this->app->singleton(ProductQueryService::class);
        $this->app->singleton(ProductCacheService::class);
        $this->app->singleton(ProductCrudService::class);
        $this->app->singleton(ProductMetricService::class);
        $this->app->singleton(ProductRentalService::class);
    }

    public function boot(): void
    {
        // Load module routes
        $this->loadRoutesFrom(__DIR__.'/routes.php');

        // Register Authorization Policy
        $this->registerPolicies();
    }

    protected function registerPolicies(): void
    {
        Gate::policy(Product::class, ProductPolicy::class);
    }
}
