<?php

use App\Services\CurrencyService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('app:update-currency-rates', function () {
    $this->comment('Updating currency rates...');
    app(CurrencyService::class)->updateRates();
    $this->info('Done!');
})->purpose('Fetch and update exchange rates');

Schedule::command('app:update-currency-rates')->daily();
