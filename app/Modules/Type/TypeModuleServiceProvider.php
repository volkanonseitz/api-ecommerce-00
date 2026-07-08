<?php

declare(strict_types=1);

namespace App\Modules\Type;

use App\Modules\Type\Actions\CreateTypeAction;
use App\Modules\Type\Actions\DeleteTypeAction;
use App\Modules\Type\Actions\UpdateTypeAction;
use App\Modules\Type\Services\TypeService;
use Illuminate\Support\ServiceProvider;

class TypeModuleServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(CreateTypeAction::class);
        $this->app->singleton(UpdateTypeAction::class);
        $this->app->singleton(DeleteTypeAction::class);
        $this->app->singleton(TypeService::class);
    }

    public function boot(): void {}
}
