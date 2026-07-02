<?php

declare(strict_types=1);

namespace App\Modules\Review;

use App\Models\Review as ReviewModel;
use App\Modules\Review\Policies\ReviewPolicy;
use App\Modules\Review\Services\ReviewService;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class ReviewModuleServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(ReviewService::class);
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/routes.php');
        $this->registerPolicies();
    }

    protected function registerPolicies(): void
    {
        Gate::policy(ReviewModel::class, ReviewPolicy::class);
    }
}
