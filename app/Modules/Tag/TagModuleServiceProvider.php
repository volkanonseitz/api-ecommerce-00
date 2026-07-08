<?php

declare(strict_types=1);

namespace App\Modules\Tag;

use App\Models\Tag as TagModel;
use App\Modules\Tag\Policies\TagPolicy;
use App\Modules\Tag\Services\TagService;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class TagModuleServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(TagService::class);
    }

    public function boot(): void
    {
        $this->registerPolicies();
    }

    protected function registerPolicies(): void
    {
        Gate::policy(TagModel::class, TagPolicy::class);
    }
}
