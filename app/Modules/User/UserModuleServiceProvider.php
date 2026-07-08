<?php

declare(strict_types=1);

namespace App\Modules\User;

use App\Models\User;
use App\Modules\User\Actions\AttemptLoginAction;
use App\Modules\User\Actions\RegisterUserAction;
use App\Modules\User\Policies\UserPolicy;
use App\Modules\User\Services\AuthService;
use App\Modules\User\Services\SocialLoginService;
use App\Modules\User\Services\UserCommandService;
use App\Modules\User\Services\UserSecurityService;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class UserModuleServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Actions
        $this->app->singleton(AttemptLoginAction::class);
        $this->app->singleton(RegisterUserAction::class);

        // Services
        $this->app->singleton(SocialLoginService::class);
        $this->app->singleton(UserCommandService::class);
        $this->app->singleton(UserSecurityService::class);
        $this->app->singleton(AuthService::class); // Depends on UserSecurityService
    }

    public function boot(): void
    {
        $this->registerPolicies();
    }

    protected function registerPolicies(): void
    {
        Gate::policy(User::class, UserPolicy::class);
    }
}
