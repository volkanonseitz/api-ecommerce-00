<?php

declare(strict_types=1);

namespace App\Modules\Refund;

use App\Models\Refund as RefundModel;
use App\Models\RefundPolicy as RefundPolicyModel;
use App\Modules\Refund\Policies\RefundPolicy;
use App\Modules\Refund\Policies\RefundPolicyPolicy;
use App\Modules\Refund\Services\RefundPolicyService;
use App\Modules\Refund\Services\RefundService;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class RefundModuleServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(RefundPolicyService::class);
        $this->app->singleton(RefundService::class);
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/routes.php');
        $this->registerPolicies();
    }

    protected function registerPolicies(): void
    {
        Gate::policy(RefundPolicyModel::class, RefundPolicyPolicy::class);
        Gate::policy(RefundModel::class, RefundPolicy::class);
    }
}
