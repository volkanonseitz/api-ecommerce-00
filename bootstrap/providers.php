<?php

use App\Modules\Order\OrderModuleServiceProvider;
use App\Modules\Payment\Providers\PaymentServiceProvider;
use App\Modules\Product\ProductModuleServiceProvider;
use App\Modules\Refund\RefundModuleServiceProvider;
use App\Modules\Review\ReviewModuleServiceProvider;
use App\Modules\Settings\SettingsModuleServiceProvider;
use App\Modules\Tag\TagModuleServiceProvider;
use App\Modules\Type\TypeModuleServiceProvider;
use App\Modules\User\UserModuleServiceProvider;
use App\Providers\AppServiceProvider;
use App\Providers\EventServiceProvider;

return [
    AppServiceProvider::class,
    EventServiceProvider::class,
    // Module Service Providers
    OrderModuleServiceProvider::class,
    PaymentServiceProvider::class,
    ProductModuleServiceProvider::class,
    RefundModuleServiceProvider::class,
    ReviewModuleServiceProvider::class,
    SettingsModuleServiceProvider::class,
    TagModuleServiceProvider::class,
    TypeModuleServiceProvider::class,
    UserModuleServiceProvider::class,
];
