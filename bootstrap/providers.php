<?php

use App\Modules\Refund\RefundModuleServiceProvider;
use App\Modules\Review\ReviewModuleServiceProvider;
use App\Modules\Settings\SettingsModuleServiceProvider;
use App\Modules\Product\ProductModuleServiceProvider;
use App\Modules\User\UserModuleServiceProvider;
use App\Modules\Type\TypeModuleServiceProvider;
use App\Modules\Order\OrderModuleServiceProvider;
use App\Modules\Tag\TagModuleServiceProvider;
use App\Modules\Payment\Providers\PaymentServiceProvider;
use App\Providers\AppServiceProvider;
use App\Providers\EventServiceProvider;

return [
    AppServiceProvider::class,
    EventServiceProvider::class,
    // Module Service Providers
    RefundModuleServiceProvider::class,
    ReviewModuleServiceProvider::class,
    SettingsModuleServiceProvider::class,
    ProductModuleServiceProvider::class,
    UserModuleServiceProvider::class,
    TypeModuleServiceProvider::class,
    OrderModuleServiceProvider::class,
    TagModuleServiceProvider::class,
    PaymentServiceProvider::class,
];
