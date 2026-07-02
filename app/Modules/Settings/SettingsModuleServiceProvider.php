<?php

declare(strict_types=1);

namespace App\Modules\Settings;

use App\Models\Settings as SettingsModel;
use App\Modules\Settings\Policies\SettingsPolicy;
use App\Modules\Settings\Services\SettingsService;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class SettingsModuleServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(SettingsService::class);
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/routes.php');
        $this->registerPolicies();
    }

    protected function registerPolicies(): void
    {
        Gate::policy(SettingsModel::class, SettingsPolicy::class);
    }
}
